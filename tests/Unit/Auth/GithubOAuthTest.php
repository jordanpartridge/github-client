<?php

use JordanPartridge\GithubClient\Auth\GithubOAuth;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Http;

describe('GithubOAuth', function () {
    describe('getAuthorizationUrl', function () {
        it('generates authorization URL with default scopes', function () {
            $oauth = new GithubOAuth(
                clientId: 'test_client_id',
                clientSecret: 'test_client_secret',
                redirectUrl: 'https://example.com/callback',
            );

            $url = $oauth->getAuthorizationUrl();

            expect($url)->toStartWith('https://github.com/login/oauth/authorize?')
                ->and($url)->toContain('client_id=test_client_id')
                ->and($url)->toContain('redirect_uri=' . urlencode('https://example.com/callback'))
                ->and($url)->toContain('scope=repo')
                ->and($url)->toContain('state=');
        });

        it('generates authorization URL with custom scopes', function () {
            $oauth = new GithubOAuth(
                clientId: 'test_client_id',
                clientSecret: 'test_client_secret',
                redirectUrl: 'https://example.com/callback',
            );

            $url = $oauth->getAuthorizationUrl(['repo', 'user', 'read:org']);

            expect($url)->toContain('scope=' . urlencode('repo user read:org'));
        });

        it('generates unique state for each call', function () {
            $oauth = new GithubOAuth(
                clientId: 'test_client_id',
                clientSecret: 'test_client_secret',
                redirectUrl: 'https://example.com/callback',
            );

            $url1 = $oauth->getAuthorizationUrl();
            $url2 = $oauth->getAuthorizationUrl();

            // Extract state values
            preg_match('/state=([^&]+)/', $url1, $matches1);
            preg_match('/state=([^&]+)/', $url2, $matches2);

            expect($matches1[1])->not->toBe($matches2[1]);
        });

        it('generates 32-character hex state', function () {
            $oauth = new GithubOAuth(
                clientId: 'test_client_id',
                clientSecret: 'test_client_secret',
                redirectUrl: 'https://example.com/callback',
            );

            $url = $oauth->getAuthorizationUrl();

            preg_match('/state=([^&]+)/', $url, $matches);
            $state = $matches[1];

            expect(strlen($state))->toBe(32)
                ->and(ctype_xdigit($state))->toBeTrue();
        });
    });

    describe('getAccessToken', function () {
        it('exchanges code for access token', function () {
            Http::fake([
                'github.com/login/oauth/access_token' => Http::response(
                    'access_token=gho_test_access_token&token_type=bearer&scope=repo',
                    200,
                ),
            ]);

            $oauth = new GithubOAuth(
                clientId: 'test_client_id',
                clientSecret: 'test_client_secret',
                redirectUrl: 'https://example.com/callback',
            );

            $token = $oauth->getAccessToken('test_auth_code');

            expect($token)->toBe('gho_test_access_token');

            Http::assertSent(function ($request) {
                return $request->url() === 'https://github.com/login/oauth/access_token'
                    && $request['client_id'] === 'test_client_id'
                    && $request['client_secret'] === 'test_client_secret'
                    && $request['code'] === 'test_auth_code'
                    && $request['redirect_uri'] === 'https://example.com/callback';
            });
        });

        it('throws exception when access token is not in response', function () {
            Http::fake([
                'github.com/login/oauth/access_token' => Http::response(
                    'error=bad_verification_code&error_description=The+code+passed+is+incorrect+or+expired.',
                    200,
                ),
            ]);

            $oauth = new GithubOAuth(
                clientId: 'test_client_id',
                clientSecret: 'test_client_secret',
                redirectUrl: 'https://example.com/callback',
            );

            expect(fn () => $oauth->getAccessToken('invalid_code'))
                ->toThrow(AuthenticationException::class, 'Failed to get access token');
        });

        it('throws exception on HTTP error', function () {
            Http::fake([
                'github.com/login/oauth/access_token' => Http::response(
                    ['message' => 'Server error'],
                    500,
                ),
            ]);

            $oauth = new GithubOAuth(
                clientId: 'test_client_id',
                clientSecret: 'test_client_secret',
                redirectUrl: 'https://example.com/callback',
            );

            expect(fn () => $oauth->getAccessToken('test_code'))
                ->toThrow(\Illuminate\Http\Client\RequestException::class);
        });

        it('handles JSON response format', function () {
            // GitHub can also return JSON format depending on Accept header
            Http::fake([
                'github.com/login/oauth/access_token' => Http::response(
                    'access_token=gho_json_token&token_type=bearer',
                    200,
                ),
            ]);

            $oauth = new GithubOAuth(
                clientId: 'test_client_id',
                clientSecret: 'test_client_secret',
                redirectUrl: 'https://example.com/callback',
            );

            $token = $oauth->getAccessToken('test_code');

            expect($token)->toBe('gho_json_token');
        });
    });

    describe('constructor', function () {
        it('accepts required parameters', function () {
            $oauth = new GithubOAuth(
                clientId: 'my_client_id',
                clientSecret: 'my_client_secret',
                redirectUrl: 'https://myapp.com/auth/callback',
            );

            expect($oauth)->toBeInstanceOf(GithubOAuth::class);
        });
    });
});
