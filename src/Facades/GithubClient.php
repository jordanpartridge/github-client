<?php

namespace JordanPartridge\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JordanPartridge\GithubClient\GithubClient
 */
class GithubClient extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JordanPartridge\GithubClient\GithubClient::class;
    }
}
