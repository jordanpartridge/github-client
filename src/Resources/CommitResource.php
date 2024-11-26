<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;
use JordanPartridge\GithubClient\Data\CommitDTO;
use JordanPartridge\GithubClient\Requests\Commits\Get;
use JordanPartridge\GithubClient\Requests\Commits\Index;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Response;

readonly class CommitResource extends BaseResource
{
    use ValidatesRepoName;

    public function all(string $repo_name): Response
    {
        return $this->validateRepo($repo_name)
            ->connector()
            ->send(new Index($repo_name));
    }

    public function get(string $repo_name, string $commit_sha): CommitDTO
    {
        $this->validateRepoName($repo_name);
        $repo = $this->dataObjectFromFullName($repo_name);
        return $this->connector()->send(new Get($repo, $commit_sha))->dto();
    }

    private function validateRepo(string $repo_name): self
    {
        $this->validateRepoName($repo_name);

        return $this;
    }

    private function dataObjectFromFullName(string $full_name): Repo
    {
        return Repo::fromFullName($full_name);
    }
}
