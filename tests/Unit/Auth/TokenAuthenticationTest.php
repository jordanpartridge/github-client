<?php

use JordanPartridge\GithubClient\Auth\TokenAuthentication;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

describe('TokenAuthentication', function () {
    describe('getAuthorizationHeader', function () {
        it('returns bearer token format', function () {
            $auth = new TokenAuthentication('ghp_test123456789012345678901234567890ab');

            expect($auth->getAuthorizationHeader())->toBe('Bearer ghp_test123456789012345678901234567890ab');
        });
    });

    describe('validate', function () {
        it('throws exception for empty token', function () {
            $auth = new TokenAuthentication('');

            expect(fn () => $auth->validate())
                ->toThrow(AuthenticationException::class);
        });

        it('throws exception for token that is too short', function () {
            $auth = new TokenAuthentication('short');

            expect(fn () => $auth->validate())
                ->toThrow(AuthenticationException::class, 'Token appears to be too short');
        });

        it('throws exception for token with invalid prefix', function () {
            $auth = new TokenAuthentication('invalid_prefix_token_12345');

            expect(fn () => $auth->validate())
                ->toThrow(AuthenticationException::class, 'Token format appears invalid');
        });

        it('validates personal access token with ghp_ prefix', function () {
            $auth = new TokenAuthentication('ghp_test123456789012345678901234567890ab');

            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });

        it('validates OAuth token with gho_ prefix', function () {
            $auth = new TokenAuthentication('gho_test123456789012345678901234567890ab');

            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });

        it('validates user-to-server token with ghu_ prefix', function () {
            $auth = new TokenAuthentication('ghu_test123456789012345678901234567890ab');

            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });

        it('validates server-to-server token with ghs_ prefix', function () {
            $auth = new TokenAuthentication('ghs_test123456789012345678901234567890ab');

            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });

        it('validates refresh token with ghr_ prefix', function () {
            $auth = new TokenAuthentication('ghr_test123456789012345678901234567890ab');

            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });

        it('validates legacy 40-character alphanumeric token', function () {
            $auth = new TokenAuthentication('a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2');

            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });

        it('rejects invalid legacy token format', function () {
            // Token with uppercase letters (not valid hex)
            $auth = new TokenAuthentication('A1B2C3D4E5F6A1B2C3D4E5F6A1B2C3D4E5F6A1B2');

            expect(fn () => $auth->validate())
                ->toThrow(AuthenticationException::class, 'Token format appears invalid');
        });
    });

    describe('getType', function () {
        it('returns token type', function () {
            $auth = new TokenAuthentication('ghp_test123456789012345678901234567890ab');

            expect($auth->getType())->toBe('token');
        });
    });

    describe('needsRefresh', function () {
        it('returns false for personal access tokens', function () {
            $auth = new TokenAuthentication('ghp_test123456789012345678901234567890ab');

            expect($auth->needsRefresh())->toBeFalse();
        });
    });

    describe('refresh', function () {
        it('does nothing for personal access tokens', function () {
            $auth = new TokenAuthentication('ghp_test123456789012345678901234567890ab');

            // Should not throw any exception
            $auth->refresh();

            expect($auth->getAuthorizationHeader())->toBe('Bearer ghp_test123456789012345678901234567890ab');
        });
    });

    describe('implements AuthenticationStrategy', function () {
        it('implements the interface', function () {
            $auth = new TokenAuthentication('ghp_test123456789012345678901234567890ab');

            expect($auth)->toBeInstanceOf(\JordanPartridge\GithubClient\Auth\AuthenticationStrategy::class);
        });
    });
});
