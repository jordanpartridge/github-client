<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Resources\RepoResource;

class Github
{
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
