<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueCommentDTO;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateComment extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $issue_number,
        protected string $bodyText,
    ) {}

    protected function defaultBody(): array
    {
        return [
            'body' => $this->bodyText,
        ];
    }

    public function createDtoFromResponse(Response $response): IssueCommentDTO
    {
        return IssueCommentDTO::fromApiResponse($response->json());
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issue_number}/comments";
    }
}
