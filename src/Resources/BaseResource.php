<?php

namespace ShadeTheory\GithubClient\Resources;

use ShadeTheory\GithubClient\Contracts\ResourceInterface;
use ShadeTheory\GithubClient\Github;

abstract readonly class BaseResource implements ResourceInterface
{
    /**
     * Create a new Resource instance
     *
     * @param  Github  $github  The authenticated GitHub client
     */
    public function __construct(
        private Github $github,
    ) {}

    /**
     * Allows access to the Github instance
     */
    public function github(): Github
    {
        return $this->github;
    }

    /**
     * Convenience method to access the connector
     */
    public function connector()
    {
        return $this->github->connector();
    }
}
