<?php

namespace JordanPartridge\GithubClient\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class User extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
