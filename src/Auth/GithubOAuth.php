<?php

namespace JordanPartridge\GithubClient\Auth;

use Illuminate\Support\Facades\Http;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

class GithubOAuth
{
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected string $redirectUrl
    ) {}

    public function getAuthorizationUrl(array $scopes = ['repo']): string
    {
        $queryParams = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => implode(' ', $scopes),
            'state' => $this->generateState(),
        ]);

        return "https://github.com/login/oauth/authorize?{$queryParams}";
    }

    public function getAccessToken(string $code): string
    {
        $response = Http::post('https://github.com/login/oauth/access_token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ])->throw();

        parse_str($response->body(), $result);

        if (! isset($result['access_token'])) {
            throw AuthenticationException::invalidToken('Failed to get access token');
        }

        return $result['access_token'];
    }

    protected function generateState(): string
    {
        return bin2hex(random_bytes(16));
    }
}
