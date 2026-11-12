<?php

use Illuminate\Support\Facades\Http;
use ShadeTheory\GithubClient\Auth\GithubOAuth;
use ShadeTheory\GithubClient\Exceptions\AuthenticationException;

describe('GitHub OAuth', function () {
    it('throws when oauth config is missing', function () {
        $oauth = new GithubOAuth(null, null, null);

        expect(fn () => $oauth->getAuthorizationUrl())
            ->toThrow(AuthenticationException::class, 'GitHub OAuth is not configured');
    });

    it('puts the caller supplied state on the authorize url', function () {
        $oauth = new GithubOAuth('client-id', 'client-secret', 'https://example.com/callback');

        $url = $oauth->getAuthorizationUrl(['repo', 'user'], 'fixed-state');

        expect($url)
            ->toStartWith('https://github.com/login/oauth/authorize?')
            ->and($url)->toContain('client_id=client-id')
            ->and($url)->toContain('state=fixed-state')
            ->and($url)->toContain('scope=repo+user');
    });

    it('exchanges an authorization code for a token', function () {
        Http::fake([
            'https://github.com/login/oauth/access_token' => function ($request) {
                expect($request['code'])->toBe('authorization-code')
                    ->and($request['client_id'])->toBe('client-id')
                    ->and($request['redirect_uri'])->toBe('https://example.com/callback');

                return Http::response('access_token=oauth-token&scope=repo&token_type=bearer');
            },
        ]);
        $oauth = new GithubOAuth('client-id', 'client-secret', 'https://example.com/callback');

        expect($oauth->getAccessToken('authorization-code'))->toBe('oauth-token');
    });

    it('rejects an OAuth response without an access token', function () {
        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response('error=bad_verification_code'),
        ]);
        $oauth = new GithubOAuth('client-id', 'client-secret', 'https://example.com/callback');

        expect(fn () => $oauth->getAccessToken('expired-code'))
            ->toThrow(AuthenticationException::class, 'Failed to get access token');
    });
});
