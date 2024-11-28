<?php

namespace JordanPartridge\GithubClient;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\RepoResource;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
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
    public function __construct(string $token)
    {
        $this->validateToken($token);
        $this->authenticate(new TokenAuthenticator($token));
    }

    /**
     * The Base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return config('github-client.base_url', 'https://api.github.com');
    }

    private function validateToken(string $token): void
    {
        if (empty(trim($token))) {
            throw new InvalidArgumentException('Token is required');
        }
    }

    /**
     * @return Data\RepoDTO|Response
     */
    public function repo(string $full_name): Repo|Response
    {
        return (new RepoResource($this))->get(Repo::fromFullName($full_name));
    }

    public function commits(): CommitResource
    {
        return new CommitResource($this);
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
