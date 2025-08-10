<?php

namespace JordanPartridge\GithubClient\Requests\Repos;

use JordanPartridge\GithubClient\ValueObjects\Repo as RepoValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class Delete extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly RepoValue $repo,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/repos/' . $this->repo->fullName();
    }
}
