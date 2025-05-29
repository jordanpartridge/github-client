<?php

namespace JordanPartridge\GithubClient\Requests\Actions;

use InvalidArgumentException;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListWorkflows extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int|null  $per_page  Items per page (max 100)
     * @param  int|null  $page  Page number
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected ?int $per_page = null,
        protected ?int $page = null,
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
        ], fn ($value) => $value !== null);
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/actions/workflows";
    }
}
