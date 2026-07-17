<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Connectors\GithubConnector;
use JordanPartridge\GithubClient\Data\RateLimitDTO;
use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Exceptions\ApiException;
use JordanPartridge\GithubClient\Exceptions\NetworkException;
use JordanPartridge\GithubClient\Requests\RateLimit\Get;
use JordanPartridge\GithubClient\Auth\GitHubAppAuthentication;
use JordanPartridge\GithubClient\Resources\ActionsResource;
use JordanPartridge\GithubClient\Resources\CommentsResource;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\InstallationsResource;
use JordanPartridge\GithubClient\Resources\IssuesResource;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
use JordanPartridge\GithubClient\Resources\ReleasesResource;
use JordanPartridge\GithubClient\Resources\RepoResource;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Response;

class Github
{
    use Concerns\ValidatesRepoName;

    public function __construct(
        protected GithubConnector $connector,
    ) {}

    public function connector(): GithubConnector
    {
        return $this->connector;
    }

    public function repos(): RepoResource
    {
        return new RepoResource($this);
    }

    public function commits(): CommitResource
    {
        return new CommitResource($this);
    }

    public function files(): FileResource
    {
        return new FileResource($this);
    }

    public function pullRequests(): PullRequestResource
    {
        return new PullRequestResource($this);
    }

    public function actions(): ActionsResource
    {
        return new ActionsResource($this);
    }

    public function issues(): IssuesResource
    {
        return new IssuesResource($this);
    }

    public function comments(): CommentsResource
    {
        return new CommentsResource($this);
    }

    public function releases(): ReleasesResource
    {
        return new ReleasesResource($this);
    }

    public function installations(): InstallationsResource
    {
        return new InstallationsResource($this);
    }

    /**
     * Get the current rate limit status for all resources.
     *
     * @return array<string, RateLimitDTO> Array of rate limit DTOs keyed by resource type
     *
     * @throws ApiException When the API request fails
     * @throws NetworkException When network connectivity issues occur
     */
    public function getRateLimitStatus(): array
    {
        try {
            $request = new Get();
            $response = $this->connector->send($request);

            if (! $response->successful()) {
                throw new ApiException($response);
            }

            return $request->createDtoFromResponse($response);
        } catch (\Exception $e) {
            if ($e instanceof ApiException) {
                throw $e;
            }
            throw new NetworkException('rate limit check', $e->getMessage(), previous: $e);
        }
    }

    /**
     * Get rate limit status for a specific resource type.
     *
     * @param  string  $resource  The resource type (core, search, graphql, etc.)
     *
     * @return RateLimitDTO The rate limit information for the specified resource
     *
     * @throws ApiException When the API request fails or resource not found
     */
    public function getRateLimitForResource(string $resource = 'core'): RateLimitDTO
    {
        $rateLimits = $this->getRateLimitStatus();

        if (! isset($rateLimits[$resource])) {
            throw new ApiException(
                response: $this->connector->send(new Get()),
                message: "Rate limit resource '{$resource}' not found",
            );
        }

        return $rateLimits[$resource];
    }

    /**
     * Check if any rate limits are exceeded.
     */
    public function hasRateLimitExceeded(): bool
    {
        try {
            $rateLimits = $this->getRateLimitStatus();

            foreach ($rateLimits as $rateLimit) {
                if ($rateLimit->isExceeded()) {
                    return true;
                }
            }

            return false;
        } catch (\Exception) {
            // If we can't check rate limits, assume we haven't exceeded them
            return false;
        }
    }

    /**
     * Get a repository by full name with automatic validation.
     *
     * @param  string  $fullName  The full repository name (owner/repo)
     *
     * @return RepoData The repository data
     */
    public function getRepo(string $fullName): RepoData
    {
        $repo = Repo::fromFullName($fullName); // Validates the name

        return $this->repos()->get($repo);
    }

    /**
     * Delete a repository by full name with automatic validation.
     *
     * @param  string  $fullName  The full repository name (owner/repo)
     *
     * @return Response The deletion response
     */
    public function deleteRepo(string $fullName): Response
    {
        $repo = Repo::fromFullName($fullName); // Ensures validation

        return $this->repos()->delete($repo);
    }

    /**
     * Create a new GitHub client authenticated as a GitHub App installation.
     *
     * This creates a new instance configured to act on behalf of a specific
     * installation, using installation tokens instead of JWT tokens.
     *
     * @param  int  $installationId  The installation ID to authenticate as
     *
     * @return self A new Github instance authenticated for the installation
     */
    public static function forInstallation(int $installationId): self
    {
        // Get GitHub App config
        $appId = config('github-client.github_app.app_id');
        $privateKey = self::resolvePrivateKey();

        if (! $appId || ! $privateKey) {
            throw new \RuntimeException(
                'GitHub App not configured. Set GITHUB_APP_ID and GITHUB_APP_PRIVATE_KEY or GITHUB_APP_PRIVATE_KEY_PATH',
            );
        }

        $auth = new GitHubAppAuthentication(
            appId: $appId,
            privateKey: $privateKey,
            installationId: (string) $installationId,
        );

        $connector = new GithubConnector($auth);

        return new self($connector);
    }

    /**
     * Create a new GitHub client with custom GitHub App credentials.
     *
     * This allows using GitHub App authentication without relying on config files.
     *
     * @param  string  $appId  The GitHub App ID
     * @param  string  $privateKey  The private key (PEM format or base64 encoded)
     * @param  int|null  $installationId  Optional installation ID
     *
     * @return self A new Github instance with GitHub App authentication
     */
    public static function withApp(string $appId, string $privateKey, ?int $installationId = null): self
    {
        $auth = new GitHubAppAuthentication(
            appId: $appId,
            privateKey: $privateKey,
            installationId: $installationId ? (string) $installationId : null,
        );

        $connector = new GithubConnector($auth);

        return new self($connector);
    }

    /**
     * Resolve the private key from config.
     */
    private static function resolvePrivateKey(): ?string
    {
        // Try direct key first
        $key = config('github-client.github_app.private_key');
        if ($key) {
            return $key;
        }

        // Try file path
        $path = config('github-client.github_app.private_key_path');
        if ($path && file_exists($path)) {
            return file_get_contents($path);
        }

        // Try base_path for relative paths
        if ($path && file_exists(base_path($path))) {
            return file_get_contents(base_path($path));
        }

        return null;
    }
}
