<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Resources\RepoResource;

class Github
{
    /**
     * @var GithubConnector
     */
    private GithubConnector $connector;

    public function __construct(GithubConnector $connector,
    ) {
        $this->connector = $connector;
    }

    public function repos(): RepoResource
    {
        return new RepoResource($this->connector);
    }
}
