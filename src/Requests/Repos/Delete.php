<?php

namespace JordanPartridge\GithubClient\Requests\Repos;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class Delete extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $repo_name,
    )
    {}

    public function resolveEndpoint(): string
    {
        return '/repos/' . $this->repo_name;
    }
}
