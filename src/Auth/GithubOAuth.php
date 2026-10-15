<?php

namespace JordanPartridge\GithubClient\Auth;

use Illuminate\Support\Facades\Http;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

class GithubOAuth
{
    public function __construct(
        protected ?string $clientId,
        protected ?string $clientSecret,
        protected ?string $redirectUrl,
    ) {}

    /**
     * @param  array<int, string>  $scopes
     */
    public function getAuthorizationUrl(array $scopes = ['repo'], ?string $state = null): string
    {
        $this->assertConfigured();

        $queryParams = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => implode(' ', $scopes),
            'state' => $state ?? $this->generateState(),
        ]);

        return "https://github.com/login/oauth/authorize?{$queryParams}";
    }

    public function getAccessToken(string $code): string
    {
        $this->assertConfigured();

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

    protected function assertConfigured(): void
    {
        if ($this->clientId === null || $this->clientId === ''
            || $this->clientSecret === null || $this->clientSecret === ''
            || $this->redirectUrl === null || $this->redirectUrl === '') {
            throw new AuthenticationException(
                'GitHub OAuth is not configured. Set GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET, and GITHUB_REDIRECT_URL.',
                'oauth',
            );
        }
    }
}
