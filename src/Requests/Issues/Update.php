<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use JordanPartridge\GithubClient\Enums\Issues\State;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class Update extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $issue_number,
        protected ?string $title = null,
        protected ?string $bodyText = null,
        protected ?State $state = null,
        protected ?array $assignees = null,
        protected ?int $milestone = null,
        protected ?array $labels = null,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter([
            'title' => $this->title,
            'body' => $this->bodyText,
            'state' => $this->state?->value,
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
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issue_number}";
    }
}
