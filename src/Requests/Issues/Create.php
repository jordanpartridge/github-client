<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class Create extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected string $title,
        protected ?string $bodyText = null,
        protected ?array $assignees = null,
        protected ?int $milestone = null,
        protected ?array $labels = null,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter([
            'title' => $this->title,
            'body' => $this->bodyText,
            'assignees' => $this->assignees,
            'milestone' => $this->milestone,
            'labels' => $this->labels,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): IssueDTO
    {
        return IssueDTO::fromApiResponse($response->json());
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues";
    }
}
