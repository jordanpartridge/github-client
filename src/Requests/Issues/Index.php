<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Issues\Sort;
use JordanPartridge\GithubClient\Enums\Issues\State;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Index extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
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

    public function createDtoFromResponse(Response $response): array
    {
        $issues = [];
        foreach ($response->json() as $item) {
            // Skip pull requests - GitHub's Issues API returns both
            if (! isset($item['pull_request'])) {
                try {
                    $issues[] = IssueDTO::fromApiResponse($item);
                } catch (\InvalidArgumentException $e) {
                    // Skip items that can't be converted to issues (e.g., PRs)
                    continue;
                }
            }
        }

        return $issues;
    }

    public function resolveEndpoint(): string
    {
        return '/user/issues';
    }
}
