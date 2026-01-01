<?php

use JordanPartridge\GithubClient\Exceptions\GithubAuthException;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use JordanPartridge\GithubClient\Exceptions\GithubClientException;

describe('GithubAuthException', function () {
    describe('deprecation', function () {
        it('extends AuthenticationException', function () {
            $exception = new GithubAuthException('Test');

            expect($exception)->toBeInstanceOf(AuthenticationException::class);
        });

        it('inherits all AuthenticationException functionality', function () {
            $exception = new GithubAuthException('Auth failed', 'oauth', 403);

            expect($exception->getMessage())->toBe('Auth failed')
                ->and($exception->getAuthenticationType())->toBe('oauth')
                ->and($exception->getCode())->toBe(403);
        });

        it('has access to static factory methods from parent', function () {
            // GithubAuthException should work just like AuthenticationException
            $exception = new GithubAuthException('Token invalid');
            $exception->addContext('source', 'legacy');

            $context = $exception->getContext();
            expect($context)->toHaveKey('authentication_type')
                ->and($context)->toHaveKey('source');
        });
    });

    describe('inheritance chain', function () {
        it('is instance of GithubClientException', function () {
            $exception = new GithubAuthException('Test');

            expect($exception)->toBeInstanceOf(GithubClientException::class);
        });

        it('is instance of Exception', function () {
            $exception = new GithubAuthException('Test');

            expect($exception)->toBeInstanceOf(Exception::class);
        });

        it('is instance of Throwable', function () {
            $exception = new GithubAuthException('Test');

            expect($exception)->toBeInstanceOf(Throwable::class);
        });
    });

    describe('backward compatibility', function () {
        it('can be caught as AuthenticationException', function () {
            $caught = false;

            try {
                throw new GithubAuthException('Legacy error');
            } catch (AuthenticationException $e) {
                $caught = true;
                expect($e->getMessage())->toBe('Legacy error');
            }

            expect($caught)->toBeTrue();
        });

        it('can be caught as GithubClientException', function () {
            $caught = false;

            try {
                throw new GithubAuthException('Legacy error');
            } catch (GithubClientException $e) {
                $caught = true;
            }

            expect($caught)->toBeTrue();
        });
    });
});
