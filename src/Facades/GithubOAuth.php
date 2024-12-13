<?php

namespace JordanPartridge\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;
use JordanPartridge\GithubClient\Auth\GithubOAuth as GithubOAuthClass;

/**
 * @method static string getAuthorizationUrl(array $scopes = ['repo'])
 * @method static string getAccessToken(string $code)
 *
 * @see GithubOAuthClass
 */
class GithubOAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GithubOAuthClass::class;
    }
}