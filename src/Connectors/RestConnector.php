<?php

namespace JordanPartridge\GithubClient\Connectors;

use JordanPartridge\GithubClient\Contracts\AbstractGithubConnector;
use JordanPartridge\GithubClient\Resources\RepoResource;

class RestConnector extends AbstractGithubConnector
{
    /**
     * Resolve the base URL for GitHub REST API.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.github.com';
    }


    public function repos(): RepoResource
    {
        return new RepoResource($this);
    }

    /**
     * List repositories for a user.
     *
     * @param string $owner
     * @param array $params
     * @return mixed
     * @throws \Saloon\Exceptions\RequestException
     */
    public function listRepositories(string $owner, array $params = [])
    {
        return $this->get("/users/{$owner}/repos", $params);
    }

    // Add more REST-specific methods as needed
}
