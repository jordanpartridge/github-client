<?php

namespace ShadeTheory\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;
use ShadeTheory\GithubClient\Auth\GithubOAuth as GithubOAuthClass;

/**
 * @method static string getAuthorizationUrl(array $scopes = ['repo'], ?string $state = null)
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
