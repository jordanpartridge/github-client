<?php

namespace JordanPartridge\GithubClient\Requests\Repos;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Data\Repo;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\RepoType;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Index extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  int|null  $per_page  Items per page (max 100)
     * @param  int|null  $page  Page number
     * @param  Visibility|null  $visibility  Can be one of: public, private, all
     * @param  Sort|null  $sort  Can be one of: created, updated, pushed, full_name
     * @param  Direction|null  $direction  Can be one of: asc, desc
     */
    public function __construct(
        protected ?int $per_page = null,
        protected ?int $page = null,
        protected ?Visibility $visibility = null,
        protected ?Sort $sort = null,
        protected ?Direction $direction = null,
        protected ?RepoType $type = null,
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
            'visibility' => $this->visibility?->value,
            'sort' => $this->sort?->value,
            'direction' => $this->direction?->value,
            'type' => $this->type,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return array_map(fn ($repo) => Repo::fromArray($repo), $response->json());
    }

    public function resolveEndpoint(): string
    {
        return '/user/repos';
    }
}
