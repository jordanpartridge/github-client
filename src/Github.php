<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Resources\CommitResource;
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

    public function commits(string $repo_name): CommitResource
    {
        $this->validateRepoName($repo_name);

        return $this->connector->commits($repo_name);
    }
}
