<?php

use JordanPartridge\GithubClient\Auth\GitHubAppAuthentication;
use JordanPartridge\GithubClient\Auth\TokenAuthentication;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

describe('Token Authentication', function () {
    it('validates personal access tokens', function () {
        $auth = new TokenAuthentication('ghp_abcdefghijklmnopqrstuvwxyz1234567890');

        $auth->validate(); // Should not throw
        expect($auth->getType())->toBe('token');
        expect($auth->needsRefresh())->toBeFalse();
    });

    it('accepts legacy 40-character tokens', function () {
        $auth = new TokenAuthentication('abcdef1234567890abcdef1234567890abcdef12');

        $auth->validate(); // Should not throw
        expect(true)->toBeTrue(); // Just verify it didn't throw
    });

    it('throws exception for empty token', function () {
        $auth = new TokenAuthentication('');

        expect(fn () => $auth->validate())
            ->toThrow(AuthenticationException::class, 'GitHub token is required but not provided');
    });

    it('throws exception for invalid token format', function () {
        $auth = new TokenAuthentication('invalid-token');

        expect(fn () => $auth->validate())
            ->toThrow(AuthenticationException::class);
    });

    it('generates correct authorization header', function () {
        $auth = new TokenAuthentication('ghp_test123');

        expect($auth->getAuthorizationHeader())->toBe('Bearer ghp_test123');
    });
});

describe('GitHub App Authentication', function () {
    it('validates app configuration', function () {
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\nMIIEpAIBAAKCAQEA...\n-----END RSA PRIVATE KEY-----";
        $auth = new GitHubAppAuthentication('12345', $privateKey);

        // This will fail because we're using a fake key, but it tests the validation logic
        expect(fn () => $auth->validate())
            ->toThrow(AuthenticationException::class, 'Invalid private key format');
    });

    it('throws exception for missing app ID', function () {
        $auth = new GitHubAppAuthentication('', 'fake-key');

        expect(fn () => $auth->validate())
            ->toThrow(AuthenticationException::class, 'App ID is required');
    });

    it('throws exception for non-numeric app ID', function () {
        $auth = new GitHubAppAuthentication('not-a-number', 'fake-key');

        expect(fn () => $auth->validate())
            ->toThrow(AuthenticationException::class, 'App ID must be numeric');
    });

    it('throws exception for missing private key', function () {
        $auth = new GitHubAppAuthentication('12345', '');

        expect(fn () => $auth->validate())
            ->toThrow(AuthenticationException::class, 'Private key is required');
    });

    it('returns correct type', function () {
        $auth = new GitHubAppAuthentication('12345', 'fake-key');

        expect($auth->getType())->toBe('github_app');
    });

    it('can set installation token', function () {
        $auth = new GitHubAppAuthentication('12345', 'fake-key', '67890');
        $expiry = new DateTimeImmutable('+1 hour');

        $auth->setInstallationToken('token123', $expiry);

        expect($auth->getInstallationId())->toBe('67890');
    });
});

describe('Authentication Exception Factory Methods', function () {
    it('creates invalid token exception', function () {
        $exception = AuthenticationException::invalidToken('Custom message');

        expect($exception->getMessage())->toBe('Custom message')
            ->and($exception->getCode())->toBe(401)
            ->and($exception->getAuthenticationType())->toBe('token');
    });

    it('creates missing token exception', function () {
        $exception = AuthenticationException::missingToken();

        expect($exception->getMessage())->toBe('GitHub token is required but not provided')
            ->and($exception->getCode())->toBe(400);
    });

    it('creates GitHub App auth failed exception', function () {
        $exception = AuthenticationException::githubAppAuthFailed('JWT generation failed');

        expect($exception->getMessage())->toBe('JWT generation failed')
            ->and($exception->getAuthenticationType())->toBe('github_app');
    });
});
