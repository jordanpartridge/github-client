<?php

namespace JordanPartridge\GithubClient\Requests\Commits;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class Index extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $repo_name,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function resolveEndpoint(): string
    {
        return '/repos/'.$this->repo_name.'/commits';
    }
}
