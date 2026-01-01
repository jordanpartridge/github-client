<?php

use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use JordanPartridge\GithubClient\Exceptions\GithubClientException;

describe('AuthenticationException', function () {
    describe('constructor', function () {
        it('sets message correctly', function () {
            $exception = new AuthenticationException('Authentication failed');

            expect($exception->getMessage())->toBe('Authentication failed');
        });

        it('defaults to token authentication type', function () {
            $exception = new AuthenticationException('Test');

            expect($exception->getAuthenticationType())->toBe('token');
        });

        it('accepts custom authentication type', function () {
            $exception = new AuthenticationException('Test', 'oauth');

            expect($exception->getAuthenticationType())->toBe('oauth');
        });

        it('defaults to 401 status code', function () {
            $exception = new AuthenticationException('Test');

            expect($exception->getCode())->toBe(401);
        });

        it('accepts custom status code', function () {
            $exception = new AuthenticationException('Test', 'token', 403);

            expect($exception->getCode())->toBe(403);
        });

        it('accepts previous exception', function () {
            $previous = new Exception('Original error');
            $exception = new AuthenticationException('Test', 'token', 401, $previous);

            expect($exception->getPrevious())->toBe($previous);
        });

        it('includes authentication type in context', function () {
            $exception = new AuthenticationException('Test', 'github_app');

            $context = $exception->getContext();
            expect($context)->toHaveKey('authentication_type')
                ->and($context['authentication_type'])->toBe('github_app');
        });
    });

    describe('getAuthenticationType', function () {
        it('returns the authentication type', function () {
            $exception = new AuthenticationException('Test', 'bearer');

            expect($exception->getAuthenticationType())->toBe('bearer');
        });
    });

    describe('invalidToken', function () {
        it('creates exception with default message', function () {
            $exception = AuthenticationException::invalidToken();

            expect($exception->getMessage())->toBe('Invalid or expired GitHub token')
                ->and($exception->getAuthenticationType())->toBe('token')
                ->and($exception->getCode())->toBe(401);
        });

        it('creates exception with custom message', function () {
            $exception = AuthenticationException::invalidToken('Token has been revoked');

            expect($exception->getMessage())->toBe('Token has been revoked');
        });
    });

    describe('missingToken', function () {
        it('creates exception with default message', function () {
            $exception = AuthenticationException::missingToken();

            expect($exception->getMessage())->toBe('GitHub token is required but not provided')
                ->and($exception->getAuthenticationType())->toBe('token')
                ->and($exception->getCode())->toBe(400);
        });

        it('creates exception with custom message', function () {
            $exception = AuthenticationException::missingToken('Please provide a token');

            expect($exception->getMessage())->toBe('Please provide a token');
        });
    });

    describe('githubAppAuthFailed', function () {
        it('creates exception with default message', function () {
            $exception = AuthenticationException::githubAppAuthFailed();

            expect($exception->getMessage())->toBe('GitHub App authentication failed')
                ->and($exception->getAuthenticationType())->toBe('github_app')
                ->and($exception->getCode())->toBe(401);
        });

        it('creates exception with custom message', function () {
            $exception = AuthenticationException::githubAppAuthFailed('Invalid private key');

            expect($exception->getMessage())->toBe('Invalid private key');
        });
    });

    describe('noTokenFound', function () {
        it('creates exception with default guidance', function () {
            $exception = AuthenticationException::noTokenFound();

            expect($exception->getMessage())->toBe('Authentication required: No GitHub token found')
                ->and($exception->getAuthenticationType())->toBe('token')
                ->and($exception->getCode())->toBe(400);
        });

        it('creates exception with custom guidance', function () {
            $exception = AuthenticationException::noTokenFound('Set GITHUB_TOKEN environment variable');

            expect($exception->getMessage())->toBe('Authentication required: Set GITHUB_TOKEN environment variable');
        });
    });

    describe('inheritance', function () {
        it('extends GithubClientException', function () {
            $exception = new AuthenticationException('Test');

            expect($exception)->toBeInstanceOf(GithubClientException::class);
        });

        it('inherits addContext functionality', function () {
            $exception = new AuthenticationException('Test');
            $exception->addContext('attempt', 3);

            $context = $exception->getContext();
            expect($context)->toHaveKey('authentication_type')
                ->and($context)->toHaveKey('attempt')
                ->and($context['attempt'])->toBe(3);
        });
    });
});
