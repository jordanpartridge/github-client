<?php

namespace JordanPartridge\GithubClient\Requests\Checks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCombinedStatus extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected string $ref,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/commits/{$this->ref}/status";
    }
}
