<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\RepoResource;

class Github
{
    use Concerns\ValidatesRepoName;

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

    public function commits(): CommitResource
    {
        return $this->connector->commits();
    }

    public function files(): FileResource
    {
        return $this->connector->files();
    }
}
