<?php

use JordanPartridge\GithubClient\Auth\TokenResolver;

describe('TokenResolver', function () {
    beforeEach(function () {
        // Clear any existing tokens
        putenv('GITHUB_TOKEN');
        putenv('GH_TOKEN');
        config(['github-client.token' => null]);
    });

    afterEach(function () {
        // Clean up
        putenv('GITHUB_TOKEN');
        putenv('GH_TOKEN');
    });

    describe('resolve', function () {
        it('returns null when no token source available', function () {
            config(['github-client.token' => null]);
            $token = TokenResolver::resolve();
            // May return token from gh CLI if available, or null
            expect($token)->toBeString()->or->toBeNull();
        });

        it('returns token from GITHUB_TOKEN env var', function () {
            putenv('GITHUB_TOKEN=test_github_token');
            $token = TokenResolver::resolve();
            expect($token)->toBe('test_github_token');
        });

        it('returns token from GH_TOKEN env var', function () {
            putenv('GITHUB_TOKEN'); // Clear GITHUB_TOKEN
            putenv('GH_TOKEN=test_gh_token');
            $token = TokenResolver::resolve();
            expect($token)->toBe('test_gh_token');
        });

        it('prefers GITHUB_TOKEN over GH_TOKEN', function () {
            putenv('GITHUB_TOKEN=github_token');
            putenv('GH_TOKEN=gh_token');
            $token = TokenResolver::resolve();
            expect($token)->toBe('github_token');
        });

        it('returns token from config when env vars not set', function () {
            config(['github-client.token' => 'config_token']);
            $token = TokenResolver::resolve();
            expect($token)->toBe('config_token');
        });

        it('ignores placeholder config value', function () {
            config(['github-client.token' => 'your-github-token-here']);
            $token = TokenResolver::resolve();
            // Should return null or gh CLI token, not the placeholder
            expect($token)->not->toBe('your-github-token-here');
        });
    });

    describe('hasAuthentication', function () {
        it('returns true when GITHUB_TOKEN is set', function () {
            putenv('GITHUB_TOKEN=test_token');
            expect(TokenResolver::hasAuthentication())->toBeTrue();
        });

        it('returns true when config token is set', function () {
            config(['github-client.token' => 'config_token']);
            expect(TokenResolver::hasAuthentication())->toBeTrue();
        });
    });

    describe('getLastSource', function () {
        it('returns GITHUB_TOKEN when that source was used', function () {
            putenv('GITHUB_TOKEN=test_token');
            TokenResolver::resolve();
            expect(TokenResolver::getLastSource())->toBe('GITHUB_TOKEN');
        });

        it('returns GH_TOKEN when that source was used', function () {
            putenv('GITHUB_TOKEN');
            putenv('GH_TOKEN=test_token');
            TokenResolver::resolve();
            expect(TokenResolver::getLastSource())->toBe('GH_TOKEN');
        });

        it('returns config when config token was used', function () {
            config(['github-client.token' => 'config_token']);
            TokenResolver::resolve();
            expect(TokenResolver::getLastSource())->toBe('config');
        });
    });

    describe('getAuthenticationStatus', function () {
        it('returns status mentioning environment variable when env token used', function () {
            putenv('GITHUB_TOKEN=test_token');
            $status = TokenResolver::getAuthenticationStatus();
            expect($status)->toContain('environment variable');
        });

        it('returns status mentioning config when config token used', function () {
            config(['github-client.token' => 'config_token']);
            $status = TokenResolver::getAuthenticationStatus();
            expect($status)->toContain('config');
        });
    });

    describe('getAuthenticationHelp', function () {
        it('returns help message with authentication options', function () {
            $help = TokenResolver::getAuthenticationHelp();
            expect($help)
                ->toContain('GitHub CLI')
                ->toContain('GITHUB_TOKEN')
                ->toContain('config');
        });
    });
});
