<?php

namespace JordanPartridge\GithubClient\Requests\PullRequests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class Index extends Request
{

    protected Method $method = Method::GET;

    /**
     * @inheritDoc
     */
    public function resolveEndpoint(): string
    {
        // TODO: Implement resolveEndpoint() method.
    }
}
