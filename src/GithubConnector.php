<?php

namespace JordanPartridge\GithubClient;

use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Traits\Plugins\AcceptsJson;

class GithubConnector extends Connector
{
    use AcceptsJson;
    use AuthorizationCodeGrant;

    public function __construct(string $token)
    {
        $this->authenticate(new TokenAuthenticator($token));
    }

    /**
     * The Base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.github.com';
    }

    /**
     * The OAuth2 configuration
     */
    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId(config('services.github.client_id'))
            ->setClientSecret(config('services.github.client_secret'))
            ->setRedirectUri(config('services.github.redirect'))
            ->setDefaultScopes(['repo', 'user'])
            ->setAuthorizeEndpoint('https://github.com/login/oauth/authorize')
            ->setTokenEndpoint('https://github.com/login/oauth/access_token')
            ->setUserEndpoint('https://api.github.com/user');
    }

    /**
     * Default headers for every request.
     *
     * @return string[]
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.github.v3+json',
        ];
    }
}
