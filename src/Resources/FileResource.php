<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Requests\Files\Index;
use Saloon\Http\Response;

readonly class FileResource extends BaseResource
{
    public function all(string $repo_name, string $commit_sha): Response
    {
        return $this->connector()->send(new Index($repo_name, $commit_sha));
    }

}
