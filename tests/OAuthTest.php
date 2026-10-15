<?php

use JordanPartridge\GithubClient\Auth\GithubOAuth;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

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
});
