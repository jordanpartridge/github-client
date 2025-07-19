<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Concerns\HandlesIssueResponses;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Issues\Sort;
use JordanPartridge\GithubClient\Enums\Issues\State;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class Index extends Request
{
    use HandlesIssueResponses;

    protected Method $method = Method::GET;

    /**
     * List issues assigned to the authenticated user across all visible repositories.
     *
     * @param  int|null  $per_page  Results per page (1-100)
     * @param  int|null  $page  Page number
     * @param  State|null  $state  Filter by issue state
     * @param  string|null  $labels  Comma-separated list of label names
     * @param  Sort|null  $sort  Sort field
     * @param  Direction|null  $direction  Sort direction
     * @param  string|null  $assignee  Filter by assignee username
     * @param  string|null  $creator  Filter by creator username
     * @param  string|null  $mentioned  Filter by mentioned username
     * @param  string|null  $since  Only show issues updated after this time (ISO 8601 format)
     *
     * @throws InvalidArgumentException When per_page is out of valid range
     */
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

    /**
     * Get the query parameters for the request.
     *
     * @return array Filtered query parameters
     */
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

    /**
     * Get the API endpoint for this request.
     *
     * @return string The GitHub API endpoint
     */
    public function resolveEndpoint(): string
    {
        return '/user/issues';
    }
}
