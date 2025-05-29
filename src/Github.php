<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Resources\ActionsResource;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
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

    public function pullRequests(): PullRequestResource
    {
        return $this->connector->pullRequests();
    }

    public function actions(): ActionsResource
    {
        return $this->connector->actions();
    }
}
