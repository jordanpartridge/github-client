<?php

namespace JordanPartridge\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JordanPartridge\GithubClient\GithubConnector
 */
class Github extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JordanPartridge\GithubClient\Github::class;
    }
}
