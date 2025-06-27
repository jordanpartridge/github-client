<?php

namespace JordanPartridge\GithubClient\Resources;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Requests\Files\Index;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Response;

readonly class FileResource extends BaseResource
{
    public function all(string $repo_name, string $commit_sha): Response
    {
        $repo = Repo::fromFullName($repo_name);

        if (! preg_match('/^[0-9a-f]{40}$/i', $commit_sha)) {
            throw new InvalidArgumentException('Invalid commit SHA format');
        }

        return $this->github()->connector()->send(new Index($repo->fullName(), $commit_sha));
    }
}
