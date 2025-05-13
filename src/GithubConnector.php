<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
use JordanPartridge\GithubClient\Resources\RepoResource;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class GithubConnector extends Connector implements GithubConnectorInterface
{
    use AcceptsJson;

    protected ?string $token;

    public function __construct(?string $token = null)
    {
        $this->token = $token;
    }

    public function resolveBaseUrl(): string
    {
        return 'https://api.github.com';
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->token ?? config('github-client.token'));
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.github.v3+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
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

    /**
     * Make a GET request to the GitHub API
     */
    public function get(string $url, array $parameters = []): array
    {
        $request = new \Saloon\Http\Connector\BodyMethods\GetSender($url, $parameters);
        $response = $this->send($request);

        return $response->json();
    }

    /**
     * Make a POST request to the GitHub API
     */
    public function post(string $url, array $parameters = []): array
    {
        $request = new \Saloon\Http\Connector\BodyMethods\PostSender($url, $parameters);
        $response = $this->send($request);

        return $response->json();
    }

    /**
     * Make a PATCH request to the GitHub API
     */
    public function patch(string $url, array $parameters = []): array
    {
        $request = new \Saloon\Http\Connector\BodyMethods\PatchSender($url, $parameters);
        $response = $this->send($request);

        return $response->json();
    }

    /**
     * Make a PUT request to the GitHub API
     */
    public function put(string $url, array $parameters = []): array
    {
        $request = new \Saloon\Http\Connector\BodyMethods\PutSender($url, $parameters);
        $response = $this->send($request);

        return $response->json();
    }

    /**
     * Make a DELETE request to the GitHub API
     */
    public function delete(string $url, array $parameters = []): array
    {
        $request = new \Saloon\Http\Connector\BodyMethods\DeleteSender($url, $parameters);
        $response = $this->send($request);

        return $response->json();
    }
}
