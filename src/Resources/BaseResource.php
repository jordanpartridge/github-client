<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Connectors\GithubConnector;
use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Contracts\ResourceInterface;

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
