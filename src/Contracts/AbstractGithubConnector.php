<?php

namespace JordanPartridge\GithubClient\Contracts;

use Saloon\Http\Connector;

abstract class AbstractGithubConnector extends Connector implements GithubConnectorInterface
{
    protected ?string $token = null;

    public function __construct(?string $token = null)
    {
        $this->token = $token;
    }

    /**
     * Set the authentication token.
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the current authentication token.
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Default headers for GitHub API.
     */
    protected function defaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/vnd.github.v3+json',
        ];

        if ($this->token) {
            $headers['Authorization'] = "token {$this->token}";
        }

        return $headers;
    }
}
