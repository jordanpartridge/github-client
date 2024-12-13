<?php

namespace JordanPartridge\GithubClient\Requests\Repos;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use JordanPartridge\GithubClient\ValueObjects\Repo;

class Issues extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected Repo $repo,
        protected ?int $per_page = null,
        protected ?int $page = null,
        protected ?string $state = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->repo->toString()}/issues";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'per_page' => $this->per_page,
            'page' => $this->page,
            'state' => $this->state,
        ]);
    }
}
