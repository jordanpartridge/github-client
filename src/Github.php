<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Connectors\GithubConnector;
use JordanPartridge\GithubClient\Data\RateLimitDTO;
use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Exceptions\ApiException;
use JordanPartridge\GithubClient\Exceptions\NetworkException;
use JordanPartridge\GithubClient\Requests\RateLimit\Get;
use JordanPartridge\GithubClient\Resources\ActionsResource;
use JordanPartridge\GithubClient\Resources\CommentsResource;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
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
            $request = new Get;
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
     * @return RateLimitDTO The rate limit information for the specified resource
     *
     * @throws ApiException When the API request fails or resource not found
     */
    public function getRateLimitForResource(string $resource = 'core'): RateLimitDTO
    {
        $rateLimits = $this->getRateLimitStatus();

        if (! isset($rateLimits[$resource])) {
            throw new ApiException(
                response: $this->connector->send(new Get),
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
     * @return Response The deletion response
     */
    public function deleteRepo(string $fullName): Response
    {
        $repo = Repo::fromFullName($fullName); // Ensures validation

        return $this->repos()->delete($repo);
    }
}
