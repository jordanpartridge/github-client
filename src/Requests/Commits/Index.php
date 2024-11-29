<?php

namespace JordanPartridge\GithubClient\Requests\Commits;

use JordanPartridge\GithubClient\Data\Commits\CommitData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Index extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $repo_name,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/repos/'.$this->repo_name.'/commits';
    }

    public function createDtoFromResponse(Response $response): array
    {
        return $response->collect()->map(function (array $commit) {
            return CommitData::from($commit);
        })->all();
    }
}
