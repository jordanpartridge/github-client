<?php

namespace ShadeTheory\GithubClient\Requests\Repos;

use ShadeTheory\GithubClient\Data\Repos\RepoData;
use ShadeTheory\GithubClient\ValueObjects\Repo as RepoValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Get extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly RepoValue $repo,
    ) {}

    public function createDtoFromResponse(Response $response): RepoData
    {
        return RepoData::fromArray($response->json());
    }

    public function resolveEndpoint(): string
    {
        return '/repos/' . $this->repo->fullName();
    }
}
