<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Get extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $issue_number,
    ) {}

    public function createDtoFromResponse(Response $response): IssueDTO
    {
        return IssueDTO::fromApiResponse($response->json());
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issue_number}";
    }
}
