<?php

namespace JordanPartridge\GithubClient\Requests\Files;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetContents extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected string $path,
        protected ?string $ref = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/contents/{$this->path}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'ref' => $this->ref,
        ], fn ($value) => $value !== null);
    }
}
