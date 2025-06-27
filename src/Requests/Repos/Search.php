<?php

namespace JordanPartridge\GithubClient\Requests\Repos;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Data\Repos\SearchRepositoriesData;
use JordanPartridge\GithubClient\Enums\Direction;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Search extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  string  $searchQuery  The search query
     * @param  string|null  $sort  Can be one of: stars, forks, help-wanted-issues, updated
     * @param  Direction|null  $order  Can be one of: asc, desc
     * @param  int|null  $per_page  Number of results per page (max 100)
     * @param  int|null  $page  Page number of the results to fetch
     */
    public function __construct(
        protected string $searchQuery,
        protected ?string $sort = null,
        protected ?Direction $order = null,
        protected ?int $per_page = null,
        protected ?int $page = null,
    ) {
        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
            throw new InvalidArgumentException('Per page must be between 1 and 100');
        }

        if ($this->sort !== null && ! in_array($this->sort, ['stars', 'forks', 'help-wanted-issues', 'updated'])) {
            throw new InvalidArgumentException('Sort must be one of: stars, forks, help-wanted-issues, updated');
        }
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'q' => $this->searchQuery,
            'sort' => $this->sort,
            'order' => $this->order?->value,
            'per_page' => $this->per_page,
            'page' => $this->page,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): SearchRepositoriesData
    {
        $data = $response->json();
        
        return new SearchRepositoriesData(
            total_count: $data['total_count'],
            incomplete_results: $data['incomplete_results'],
            items: array_map(fn ($repo) => RepoData::from($repo), $data['items'])
        );
    }

    public function resolveEndpoint(): string
    {
        return '/search/repositories';
    }
}