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
     * Create and send a Saloon request for the given HTTP method and URL
     *
     * @param \Saloon\Enums\Method $method The HTTP method
     * @param string $url The endpoint URL
     * @param array $parameters Query or body parameters
     * @return array Response data as array
     */
    private function sendRequest(\Saloon\Enums\Method $method, string $url, array $parameters = []): array
    {
        $request = new class($method, $url, $parameters) extends \Saloon\Http\Request {
            protected \Saloon\Enums\Method $method;
            protected string $endpoint;
            protected array $parameters;
            
            public function __construct(
                \Saloon\Enums\Method $method,
                string $endpoint,
                array $parameters = []
            ) {
                $this->method = $method;
                $this->endpoint = $endpoint;
                $this->parameters = $parameters;
            }
            
            public function resolveEndpoint(): string
            {
                return $this->endpoint;
            }
            
            protected function defaultQuery(): array
            {
                if ($this->method === \Saloon\Enums\Method::GET || $this->method === \Saloon\Enums\Method::DELETE) {
                    return $this->parameters;
                }
                
                return [];
            }
            
            protected function defaultBody(): array
            {
                if ($this->method !== \Saloon\Enums\Method::GET && $this->method !== \Saloon\Enums\Method::DELETE) {
                    return $this->parameters;
                }
                
                return [];
            }
        };
        
        return $this->send($request)->json();
    }
    
    /**
     * Make a GET request to the GitHub API
     *
     * @param string $url The endpoint URL
     * @param array $parameters Query parameters
     * @return array Response data as array
     */
    public function get(string $url, array $parameters = []): array
    {
        return $this->sendRequest(\Saloon\Enums\Method::GET, $url, $parameters);
    }
    
    /**
     * Make a POST request to the GitHub API
     *
     * @param string $url The endpoint URL
     * @param array $parameters Request body data
     * @return array Response data as array
     */
    public function post(string $url, array $parameters = []): array
    {
        return $this->sendRequest(\Saloon\Enums\Method::POST, $url, $parameters);
    }
    
    /**
     * Make a PATCH request to the GitHub API
     *
     * @param string $url The endpoint URL
     * @param array $parameters Request body data
     * @return array Response data as array
     */
    public function patch(string $url, array $parameters = []): array
    {
        return $this->sendRequest(\Saloon\Enums\Method::PATCH, $url, $parameters);
    }
    
    /**
     * Make a PUT request to the GitHub API
     *
     * @param string $url The endpoint URL
     * @param array $parameters Request body data
     * @return array Response data as array
     */
    public function put(string $url, array $parameters = []): array
    {
        return $this->sendRequest(\Saloon\Enums\Method::PUT, $url, $parameters);
    }
    
    /**
     * Make a DELETE request to the GitHub API
     *
     * @param string $url The endpoint URL
     * @param array $parameters Query parameters
     * @return array Response data as array
     */
    public function delete(string $url, array $parameters = []): array
    {
        return $this->sendRequest(\Saloon\Enums\Method::DELETE, $url, $parameters);
    }
}