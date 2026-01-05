<?php

namespace JordanPartridge\GithubClient\Connectors;

use JordanPartridge\GithubClient\Auth\AuthenticationStrategy;
use JordanPartridge\GithubClient\Auth\GitHubAppAuthentication;
use JordanPartridge\GithubClient\Auth\TokenResolver;
use JordanPartridge\GithubClient\Exceptions\ApiException;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use JordanPartridge\GithubClient\Exceptions\NetworkException;
use JordanPartridge\GithubClient\Exceptions\RateLimitException;
use JordanPartridge\GithubClient\Exceptions\ResourceNotFoundException;
use JordanPartridge\GithubClient\Exceptions\ValidationException;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * GitHub API connector with optional authentication support.
 *
 * This connector supports:
 * - Optional authentication (public repos don't need auth)
 * - Multiple authentication sources (CLI, env, config)
 * - Helpful error messages for rate limits
 * - Context-aware exception handling
 */
class GithubConnector extends Connector
{
    use AcceptsJson;

    protected ?string $token;
    protected ?string $tokenSource;
    protected ?AuthenticationStrategy $authStrategy = null;

    /**
     * Create a new GitHub connector.
     *
     * @param  string|AuthenticationStrategy|null  $token  Token, auth strategy, or null to auto-resolve
     */
    public function __construct(string|AuthenticationStrategy|null $token = null)
    {
        if ($token instanceof AuthenticationStrategy) {
            $this->authStrategy = $token;
            $this->token = null;
            $this->tokenSource = $token->getType();

            // Inject connector into auth strategy if it's a GitHub App
            if ($token instanceof GitHubAppAuthentication) {
                $token->setConnector($this);
            }
        } elseif ($token !== null) {
            $this->token = $token;
            $this->tokenSource = 'explicit';
        } else {
            // Try to resolve token from multiple sources
            $this->token = TokenResolver::resolve();
            $this->tokenSource = $this->token ? TokenResolver::getLastSource() : null;
        }
    }

    /**
     * The GitHub API base URL.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.github.com';
    }

    /**
     * Default headers for all requests.
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.github.v3+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
    }

    /**
     * Configure authentication if available.
     * Returns null for unauthenticated requests (public repos).
     */
    protected function defaultAuth(): ?Authenticator
    {
        // Use auth strategy if set
        if ($this->authStrategy) {
            // Check if token needs refresh
            if ($this->authStrategy->needsRefresh()) {
                $this->authStrategy->refresh();
            }

            // Get the authorization header value
            $authHeader = $this->authStrategy->getAuthorizationHeader();

            // Extract token from "Bearer <token>" format
            $token = str_replace('Bearer ', '', $authHeader);

            return new TokenAuthenticator($token);
        }

        // Fall back to simple token authentication
        if (! $this->token) {
            return null;
        }

        return new TokenAuthenticator($this->token);
    }

    /**
     * Check if the connector is authenticated.
     */
    public function isAuthenticated(): bool
    {
        return $this->authStrategy !== null || ! empty($this->token);
    }

    /**
     * Get the authentication source.
     */
    public function getAuthenticationSource(): ?string
    {
        return $this->tokenSource;
    }

    /**
     * Get authentication status for debugging.
     */
    public function getAuthenticationStatus(): array
    {
        if (! $this->token) {
            return [
                'authenticated' => false,
                'source' => null,
                'message' => 'No authentication configured. Using public API access (60 requests/hour).',
            ];
        }

        return [
            'authenticated' => true,
            'source' => $this->tokenSource,
            'token_preview' => substr($this->token, 0, 7) . '...',
            'message' => "Authenticated via {$this->tokenSource} (5,000 requests/hour).",
        ];
    }

    /**
     * Determine if a request has failed.
     */
    public function hasRequestFailed(Response $response): bool
    {
        return $response->failed();
    }

    /**
     * Map response status codes to appropriate exceptions.
     */
    public function getRequestException(Response $response, ?\Throwable $senderException = null): ?\Throwable
    {
        $status = $response->status();
        $data = $response->json();
        $message = $data['message'] ?? '';

        return match ($status) {
            401 => $this->handleAuthenticationError($response, $message),
            403 => $this->handleForbiddenError($response, $message),
            404 => new ResourceNotFoundException(
                message: $message ?: 'GitHub resource not found',
                response: $response,
            ),
            422 => $this->handleValidationError($response, $data),
            429 => $this->handleRateLimitError($response, $message),
            500, 502, 503, 504 => new NetworkException(
                operation: 'GitHub API request',
                message: "Server error ({$status}): {$message}",
                previous: $senderException,
            ),
            default => new ApiException(
                response: $response,
                message: $message ?: "Unexpected error (HTTP {$status})",
                previous: $senderException,
            ),
        };
    }

    /**
     * Handle 401 authentication errors.
     */
    protected function handleAuthenticationError(Response $response, string $message): AuthenticationException
    {
        $helpMessage = $message ?: 'GitHub authentication failed';

        if (! $this->token) {
            $helpMessage .= "\n\nThis endpoint requires authentication. ";
            $helpMessage .= TokenResolver::getAuthenticationHelp();
        } else {
            $helpMessage .= "\n\nYour token may be invalid or expired.";
            $helpMessage .= "\nCurrent auth source: {$this->tokenSource}";
        }

        return new AuthenticationException(
            message: $helpMessage,
            authenticationType: $this->tokenSource ?? 'none',
        );
    }

    /**
     * Handle 403 forbidden errors (could be rate limit or permissions).
     */
    protected function handleForbiddenError(Response $response, string $message): \Throwable
    {
        // Check if this is a rate limit issue
        if ($this->isRateLimited($response, $message)) {
            return $this->handleRateLimitError($response, $message);
        }

        // Otherwise it's a permissions issue
        $helpMessage = $message ?: 'Access to this GitHub resource is forbidden';

        if ($this->token) {
            $helpMessage .= "\n\nYour token may not have the required scopes for this operation.";
            $helpMessage .= "\nCheck that your token has the necessary permissions.";
        } else {
            $helpMessage .= "\n\nThis resource may require authentication.";
            $helpMessage .= "\n" . TokenResolver::getAuthenticationHelp();
        }

        return new ApiException(
            response: $response,
            message: $helpMessage,
        );
    }

    /**
     * Check if a 403 response is due to rate limiting.
     */
    protected function isRateLimited(Response $response, string $message): bool
    {
        // Check message for rate limit text
        if (stripos($message, 'rate limit') !== false) {
            return true;
        }

        // Check headers for rate limit
        $remaining = $response->header('X-RateLimit-Remaining');
        if ($remaining !== null && (int) $remaining === 0) {
            return true;
        }

        return false;
    }

    /**
     * Handle rate limit errors with helpful context.
     */
    protected function handleRateLimitError(Response $response, string $message): RateLimitException
    {
        $headers = $response->headers();
        $remaining = (int) ($headers->get('X-RateLimit-Remaining') ?? 0);
        $limit = (int) ($headers->get('X-RateLimit-Limit') ?? ($this->token ? 5000 : 60));
        $reset = $headers->get('X-RateLimit-Reset');
        $resetTime = $reset ? new \DateTimeImmutable('@' . $reset) : new \DateTimeImmutable('+1 hour');

        // Build helpful message
        $helpMessage = $message ?: 'GitHub API rate limit exceeded';

        if (! $this->token) {
            $helpMessage .= "\n\nYou're using unauthenticated requests (60/hour limit).";
            $helpMessage .= "\n" . TokenResolver::getAuthenticationHelp();
        } else {
            $helpMessage .= "\n\nAuthenticated via: {$this->tokenSource}";
            $helpMessage .= "\nRate limit: {$remaining}/{$limit} requests remaining";
            $helpMessage .= "\nResets at: " . $resetTime->format('Y-m-d H:i:s T');
            $helpMessage .= "\n\nConsider implementing caching or reducing API calls.";
        }

        return new RateLimitException(
            remainingRequests: $remaining,
            resetTime: $resetTime,
            totalLimit: $limit,
            message: $helpMessage,
        );
    }

    /**
     * Handle validation errors with field-specific details.
     */
    protected function handleValidationError(Response $response, array $data): ValidationException
    {
        $message = $data['message'] ?? 'Validation failed';
        $errors = $data['errors'] ?? [];

        // Build detailed error message
        if (! empty($errors)) {
            $message .= "\n\nValidation errors:";
            foreach ($errors as $error) {
                $field = $error['field'] ?? 'unknown';
                $code = $error['code'] ?? 'invalid';
                $resource = $error['resource'] ?? '';
                $message .= "\n  - {$field}: {$code}";
                if ($resource) {
                    $message .= " (resource: {$resource})";
                }
            }
        }

        return new ValidationException(
            message: $message,
            field: $errors[0]['field'] ?? null,
            value: $errors[0]['value'] ?? null,
        );
    }
}
