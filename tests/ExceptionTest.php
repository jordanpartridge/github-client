<?php

use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use JordanPartridge\GithubClient\Exceptions\GithubClientException;
use JordanPartridge\GithubClient\Exceptions\NetworkException;
use JordanPartridge\GithubClient\Exceptions\RateLimitException;
use JordanPartridge\GithubClient\Exceptions\ValidationException;

describe('Exception Hierarchy', function () {
    it('base exception includes context', function () {
        $exception = new class ('Test message', 123, null, ['key' => 'value']) extends GithubClientException {};

        expect($exception->getMessage())->toBe('Test message')
            ->and($exception->getCode())->toBe(123)
            ->and($exception->getContext())->toBe(['key' => 'value']);
    });

    it('can add context to exception', function () {
        $exception = new class ('Test') extends GithubClientException {};
        $exception->addContext('request_id', '12345');

        expect($exception->getContext())->toBe(['request_id' => '12345']);
    });
});

describe('ValidationException', function () {
    it('includes field and value information', function () {
        $exception = new ValidationException('per_page', 200, 'Must be between 1 and 100');

        expect($exception->getField())->toBe('per_page')
            ->and($exception->getValue())->toBe(200)
            ->and($exception->getMessage())->toContain('per_page')
            ->and($exception->getCode())->toBe(422);
    });

    it('includes context with validation details', function () {
        $exception = new ValidationException('issue_number', -1, 'Must be positive');

        $context = $exception->getContext();
        expect($context)->toHaveKey('field')
            ->and($context)->toHaveKey('value')
            ->and($context)->toHaveKey('validation_message')
            ->and($context['field'])->toBe('issue_number')
            ->and($context['value'])->toBe(-1);
    });
});

describe('RateLimitException', function () {
    it('includes rate limit information', function () {
        $resetTime = new DateTimeImmutable('+1 hour');
        $exception = new RateLimitException(0, $resetTime, 5000);

        expect($exception->getRemainingRequests())->toBe(0)
            ->and($exception->getTotalLimit())->toBe(5000)
            ->and($exception->getResetTime())->toBe($resetTime)
            ->and($exception->getCode())->toBe(429);
    });

    it('calculates seconds until reset', function () {
        $resetTime = new DateTimeImmutable('+3600 seconds');
        $exception = new RateLimitException(0, $resetTime, 5000);

        $secondsUntilReset = $exception->getSecondsUntilReset();
        expect($secondsUntilReset)->toBeGreaterThan(3590)
            ->and($secondsUntilReset)->toBeLessThanOrEqual(3600);
    });

    it('includes context with rate limit details', function () {
        $resetTime = new DateTimeImmutable('+1 hour');
        $exception = new RateLimitException(10, $resetTime, 5000);

        $context = $exception->getContext();
        expect($context)->toHaveKey('remaining_requests')
            ->and($context)->toHaveKey('total_limit')
            ->and($context)->toHaveKey('reset_time')
            ->and($context)->toHaveKey('seconds_until_reset');
    });
});

describe('ApiException', function () {
    it('has proper structure', function () {
        // For now, just test that the class exists and has the expected interface
        expect(class_exists('JordanPartridge\\GithubClient\\Exceptions\\ApiException'))->toBeTrue();
    });
});

describe('NetworkException', function () {
    it('includes operation context', function () {
        $exception = new NetworkException('fetch repositories', 'Connection timeout');

        expect($exception->getOperation())->toBe('fetch repositories')
            ->and($exception->getMessage())->toContain('Network error during fetch repositories');
    });

    it('creates timeout exception', function () {
        $exception = NetworkException::timeout('API call', 30);

        expect($exception->getMessage())->toContain('timed out after 30 seconds')
            ->and($exception->getCode())->toBe(408);
    });

    it('creates connection failed exception', function () {
        $exception = NetworkException::connectionFailed('GitHub API', 'DNS resolution failed');

        expect($exception->getMessage())->toContain('Connection failed: DNS resolution failed')
            ->and($exception->getCode())->toBe(503);
    });
});

describe('AuthenticationException', function () {
    it('includes authentication type', function () {
        $exception = new AuthenticationException('Invalid token', 'github_app');

        expect($exception->getAuthenticationType())->toBe('github_app')
            ->and($exception->getCode())->toBe(401);
    });

    it('defaults to token authentication type', function () {
        $exception = new AuthenticationException('Invalid credentials');

        expect($exception->getAuthenticationType())->toBe('token');
    });
});
