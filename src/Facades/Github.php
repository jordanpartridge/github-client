<?php

namespace JordanPartridge\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;
use JordanPartridge\GithubClient\GithubConnector as GithubClientAlias;

/**
 * @see \JordanPartridge\GithubClient\GithubConnector
 */
class Github extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GithubClientAlias::class;
    }
}
