<?php

use JordanPartridge\GithubClient\Exceptions\RateLimitException;
use JordanPartridge\GithubClient\Exceptions\GithubClientException;

describe('RateLimitException', function () {
    describe('constructor', function () {
        it('sets remaining requests', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(10, $resetTime, 5000);

            expect($exception->getRemainingRequests())->toBe(10);
        });

        it('sets reset time', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception->getResetTime())->toBe($resetTime);
        });

        it('sets total limit', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception->getTotalLimit())->toBe(5000);
        });

        it('generates default message when empty', function () {
            $resetTime = new DateTimeImmutable('2024-01-15 12:30:00', new DateTimeZone('UTC'));
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception->getMessage())->toContain('GitHub API rate limit exceeded')
                ->and($exception->getMessage())->toContain('0/5000 requests remaining')
                ->and($exception->getMessage())->toContain('2024-01-15 12:30:00');
        });

        it('uses custom message when provided', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000, 'Custom rate limit message');

            expect($exception->getMessage())->toBe('Custom rate limit message');
        });

        it('defaults to 429 status code', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception->getCode())->toBe(429);
        });

        it('accepts custom status code', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000, '', 403);

            expect($exception->getCode())->toBe(403);
        });

        it('accepts previous exception', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $previous = new Exception('Original');
            $exception = new RateLimitException(0, $resetTime, 5000, '', 429, $previous);

            expect($exception->getPrevious())->toBe($previous);
        });

        it('includes rate limit details in context', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(100, $resetTime, 5000);

            $context = $exception->getContext();
            expect($context)->toHaveKey('remaining_requests')
                ->and($context)->toHaveKey('reset_time')
                ->and($context)->toHaveKey('total_limit')
                ->and($context)->toHaveKey('seconds_until_reset')
                ->and($context['remaining_requests'])->toBe(100)
                ->and($context['total_limit'])->toBe(5000);
        });
    });

    describe('getRemainingRequests', function () {
        it('returns the remaining requests count', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(42, $resetTime, 5000);

            expect($exception->getRemainingRequests())->toBe(42);
        });

        it('handles zero remaining requests', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception->getRemainingRequests())->toBe(0);
        });
    });

    describe('getResetTime', function () {
        it('returns the reset time', function () {
            $resetTime = new DateTimeImmutable('2024-06-15 15:00:00');
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception->getResetTime())->toBe($resetTime);
        });
    });

    describe('getTotalLimit', function () {
        it('returns the total limit for authenticated users', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception->getTotalLimit())->toBe(5000);
        });

        it('returns the total limit for unauthenticated users', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 60);

            expect($exception->getTotalLimit())->toBe(60);
        });
    });

    describe('getSecondsUntilReset', function () {
        it('calculates seconds until reset', function () {
            $resetTime = new DateTimeImmutable('+3600 seconds');
            $exception = new RateLimitException(0, $resetTime, 5000);

            $seconds = $exception->getSecondsUntilReset();
            // Allow some tolerance for test execution time
            expect($seconds)->toBeGreaterThan(3590)
                ->and($seconds)->toBeLessThanOrEqual(3600);
        });

        it('returns zero for past reset times', function () {
            $resetTime = new DateTimeImmutable('-1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception->getSecondsUntilReset())->toBe(0);
        });

        it('handles reset time in near future', function () {
            $resetTime = new DateTimeImmutable('+10 seconds');
            $exception = new RateLimitException(0, $resetTime, 5000);

            $seconds = $exception->getSecondsUntilReset();
            expect($seconds)->toBeGreaterThanOrEqual(5)
                ->and($seconds)->toBeLessThanOrEqual(10);
        });
    });

    describe('inheritance', function () {
        it('extends GithubClientException', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000);

            expect($exception)->toBeInstanceOf(GithubClientException::class);
        });

        it('inherits addContext functionality', function () {
            $resetTime = new DateTimeImmutable('+1 hour');
            $exception = new RateLimitException(0, $resetTime, 5000);
            $exception->addContext('endpoint', '/repos');

            $context = $exception->getContext();
            expect($context)->toHaveKey('endpoint')
                ->and($context['endpoint'])->toBe('/repos');
        });
    });
});
