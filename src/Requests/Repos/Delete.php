<?php

namespace ShadeTheory\GithubClient\Requests\Repos;

use ShadeTheory\GithubClient\ValueObjects\Repo as RepoValue;
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
