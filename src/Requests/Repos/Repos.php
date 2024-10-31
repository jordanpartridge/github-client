<?php

namespace JordanPartridge\GithubClient\Requests\Repos;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class Repos extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/user/repos';
    }
}
