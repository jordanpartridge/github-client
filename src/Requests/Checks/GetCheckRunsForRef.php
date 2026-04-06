<?php

namespace JordanPartridge\GithubClient\Requests\Checks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCheckRunsForRef extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected string $ref,
        protected ?int $perPage = null,
        protected ?int $page = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/commits/{$this->ref}/check-runs";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'per_page' => $this->perPage,
            'page' => $this->page,
        ], fn ($value) => $value !== null);
    }
}
