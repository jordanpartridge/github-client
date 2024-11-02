<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;
use JordanPartridge\GithubClient\Requests\Repos\Index;

readonly class CommitResource extends BaseResource
{
    use ValidatesRepoName;

    public function all(string $repo_name): array
    {
        $this->validateRepoName($repo_name);
        $this->connector()->send(new Index($this->repo));
    }
}
