<?php

namespace JordanPartridge\GithubClient;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\RepoResource;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Traits\Plugins\AcceptsJson;

class GithubConnector extends Connector implements GithubConnectorInterface
{
    use AcceptsJson;
    use AuthorizationCodeGrant;

    /**
     * Token can be passed in the constructor, this can be generated from the Github Developer Settings.
     *
     * @see https://github.com/settings/tokens
     */
    public function __construct(?string $token = null)
    {
        if ($token) {
            $this->validateToken($token);
            $this->authenticate(new TokenAuthenticator($token));
        }
    }

    /**
     * The Base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        if (! config()->has('github-client.base_url')) {
            Log::info('Using default GitHub API URL as no custom URL configured');
        }

        return config('github-client.base_url', 'https://api.github.com');
    }

    private function validateToken(string $token): void
    {
        if (empty(trim($token))) {
            throw new InvalidArgumentException('Token is required');
        }
    }

    public function commits(): CommitResource
    {
        return new CommitResource($this);
    }

    public function files(): FileResource
    {
        return new FileResource($this);
    }

    /**
     * Default headers for every request.
     *
     * @return string[]
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.github.v3+json',
        ];
    }

    public function repos(): RepoResource
    {
        return new RepoResource($this);
    }
}
