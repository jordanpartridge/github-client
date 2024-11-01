<?php

namespace JordanPartridge\GithubClient;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Traits\Plugins\AcceptsJson;

class GithubConnector extends Connector implements GithubConnectorInterface
{
    use AcceptsJson;
    use AuthorizationCodeGrant;

    /**
     * Token can be passed in the constructor, this can be generated from the Github Developer Settings.
     *
     * @see https://github.com/settings/tokens
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->validateToken($token);
        $this->authenticate(new TokenAuthenticator($token));
    }

    /**
     * The Base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.github.com';
    }

    private function validateToken(string $token): void
    {
        if (empty($token)) {
            throw new InvalidArgumentException('Token is required');
        }
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
