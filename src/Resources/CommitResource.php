<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;
use JordanPartridge\GithubClient\Requests\Commits\Index;
use Saloon\Http\Response;

readonly class CommitResource extends BaseResource
{
    use ValidatesRepoName;

    public function all(string $repo_name): Response
    {
        $this->validateRepoName($repo_name);

        return $this->connector()->send(new Index($repo_name));
    }
}
