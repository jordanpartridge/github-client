<?php

namespace JordanPartridge\GithubClient\Auth;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

/**
 * GitHub App authentication strategy using JWT tokens.
 */
class GitHubAppAuthentication implements AuthenticationStrategy
{
    private ?string $installationToken = null;

    private ?DateTimeImmutable $installationTokenExpiry = null;

    private ?object $connector = null;

    public function __construct(
        private readonly string $appId,
        private readonly string $privateKey,
        private readonly ?string $installationId = null,
    ) {}

    public function getAuthorizationHeader(): string
    {
        if ($this->installationId && $this->hasValidInstallationToken()) {
            return "Bearer {$this->installationToken}";
        }

        // Use JWT token for app-level authentication
        return "Bearer {$this->generateJwtToken()}";
    }

    public function validate(): void
    {
        if (empty($this->appId)) {
            throw AuthenticationException::githubAppAuthFailed('App ID is required');
        }

        if (! is_numeric($this->appId)) {
            throw AuthenticationException::githubAppAuthFailed('App ID must be numeric');
        }

        if (empty($this->privateKey)) {
            throw AuthenticationException::githubAppAuthFailed('Private key is required');
        }

        // Validate private key format
        if (! $this->isValidPrivateKey()) {
            throw AuthenticationException::githubAppAuthFailed('Invalid private key format');
        }
    }

    public function getType(): string
    {
        return 'github_app';
    }

    public function needsRefresh(): bool
    {
        if (! $this->installationId) {
            return false; // App-level JWT tokens are generated fresh each time
        }

        return ! $this->hasValidInstallationToken();
    }

    public function refresh(): void
    {
        if ($this->installationId) {
            $this->refreshInstallationToken();
        }
    }

    /**
     * Generate a JWT token for GitHub App authentication.
     */
    private function generateJwtToken(): string
    {
        $now = new DateTimeImmutable();
        $expiry = $now->modify('+10 minutes'); // GitHub recommends max 10 minutes

        $payload = [
            'iat' => $now->getTimestamp(),
            'exp' => $expiry->getTimestamp(),
            'iss' => $this->appId,
        ];

        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    /**
     * Check if we have a valid installation token.
     */
    private function hasValidInstallationToken(): bool
    {
        if (! $this->installationToken || ! $this->installationTokenExpiry) {
            return false;
        }

        // Add 5-minute buffer before expiry
        $bufferTime = $this->installationTokenExpiry->modify('-5 minutes');

        return new DateTimeImmutable() < $bufferTime;
    }

    /**
     * Refresh the installation token.
     *
     * Note: This method requires the connector to be set via setConnector()
     * before calling refresh(). The connector is typically injected when
     * used with GithubConnector.
     */
    private function refreshInstallationToken(): void
    {
        if (! $this->installationId) {
            throw AuthenticationException::githubAppAuthFailed('Installation ID required for installation token');
        }

        // Import the required classes
        $createTokenRequest = new \JordanPartridge\GithubClient\Requests\Installations\CreateAccessToken(
            (int) $this->installationId,
        );

        // Make the API call to get the installation token
        // This will use JWT authentication (app-level) to get the installation token
        try {
            $response = $this->makeApiRequest($createTokenRequest);

            if (! $response->successful()) {
                throw AuthenticationException::githubAppAuthFailed(
                    'Failed to refresh installation token: ' . ($response->json('message') ?? 'Unknown error'),
                );
            }

            $data = $response->json();
            $this->installationToken = $data['token'];
            $this->installationTokenExpiry = new DateTimeImmutable($data['expires_at']);
        } catch (\Exception $e) {
            throw AuthenticationException::githubAppAuthFailed(
                'Failed to refresh installation token: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Make an API request (to be implemented by connector integration).
     *
     * @throws AuthenticationException
     */
    private function makeApiRequest(object $request): mixed
    {
        if (! isset($this->connector)) {
            throw AuthenticationException::githubAppAuthFailed(
                'Connector not set. Cannot refresh installation token without connector.',
            );
        }

        return $this->connector->send($request);
    }

    /**
     * Set the connector for making API requests.
     */
    public function setConnector(object $connector): void
    {
        $this->connector = $connector;
    }

    /**
     * Validate the private key format.
     */
    private function isValidPrivateKey(): bool
    {
        // Check if it's a valid PEM format
        if (str_contains($this->privateKey, '-----BEGIN')) {
            return openssl_pkey_get_private($this->privateKey) !== false;
        }

        // Check if it might be a base64 encoded key
        $decoded = base64_decode($this->privateKey, true);
        if ($decoded !== false && str_contains($decoded, '-----BEGIN')) {
            return openssl_pkey_get_private($decoded) !== false;
        }

        return false;
    }

    /**
     * Set an installation token directly (for when fetched externally).
     */
    public function setInstallationToken(string $token, DateTimeImmutable $expiry): void
    {
        $this->installationToken = $token;
        $this->installationTokenExpiry = $expiry;
    }

    /**
     * Get the App ID.
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Get the Installation ID if set.
     */
    public function getInstallationId(): ?string
    {
        return $this->installationId;
    }
}
