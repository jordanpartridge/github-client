<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Concerns\HandlesIssueResponses;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Issues\Sort;
use JordanPartridge\GithubClient\Enums\Issues\State;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class RepoIndex extends Request
{
    use HandlesIssueResponses;

    protected Method $method = Method::GET;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected ?int $per_page = null,
        protected ?int $page = null,
        protected ?State $state = null,
        protected ?string $labels = null,
        protected ?Sort $sort = null,
        protected ?Direction $direction = null,
        protected ?string $assignee = null,
        protected ?string $creator = null,
        protected ?string $mentioned = null,
        protected ?string $since = null,
    ) {
        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
            throw new InvalidArgumentException('Per page must be between 1 and 100');
        }
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'per_page' => $this->per_page,
            'page' => $this->page,
            'state' => $this->state?->value,
            'labels' => $this->labels,
            'sort' => $this->sort?->value,
            'direction' => $this->direction?->value,
            'assignee' => $this->assignee,
            'creator' => $this->creator,
            'mentioned' => $this->mentioned,
            'since' => $this->since,
        ], fn ($value) => $value !== null);
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues";
    }
}
