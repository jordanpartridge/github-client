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
     *
     * @param string $token
     * @return self
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the current authentication token.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Default headers for GitHub API.
     *
     * @return array
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
