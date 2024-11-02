<?php

namespace JordanPartridge\GithubClient\Requests\Repos;

use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class Get extends Request
{
    use ValidatesRepoName;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $repo_name
    ) {
        $this->validateRepoName($repo_name);
    }

    public function resolveEndpoint(): string
    {
        return '/repos/'.$this->repo_name;
    }
}
