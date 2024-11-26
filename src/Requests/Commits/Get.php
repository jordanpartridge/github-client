<?php

namespace JordanPartridge\GithubClient\Requests\Commits;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class Get extends Request
{

    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $commit_sha,
    ) {

    }

    public function resolveEndpoint(): string
    {
        return '/commits/'.$this->commit_sha;
    }


}
