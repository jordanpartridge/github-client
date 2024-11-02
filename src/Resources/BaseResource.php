<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Contracts\ResourceInterface;
use JordanPartridge\GithubClient\GithubConnector;

abstract readonly class BaseResource implements ResourceInterface
{
    /**
     * Create a new RepoResource instance
     *
     * @param  GithubConnector  $connector  The authenticated GitHub API connector
     */
    public function __construct(
        private GithubConnectorInterface $connector,
    ) {}

    /**
     * Allows access to the GithubConnector instance
     */
    public function connector(): GithubConnectorInterface
    {
        return $this->connector;
    }
}
