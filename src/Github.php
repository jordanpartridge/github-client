<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Resources\RepoResource;

class Github
{
    public function __construct(
        protected GithubConnectorInterface $connector,
    ) {}

    public function connector(): GithubConnectorInterface
    {
        return $this->connector;
    }

    public function repos(): RepoResource
    {
        return $this->connector->repos();
    }
}
