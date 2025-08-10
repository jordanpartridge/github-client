<?php

use JordanPartridge\GithubClient\Data\RateLimitDTO;
use JordanPartridge\GithubClient\Exceptions\ApiException;
use JordanPartridge\GithubClient\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('Rate Limit Functionality', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient;
        $this->github = app(Github::class);
        $this->github->connector()->withMockClient($this->mockClient);
    });

    it('can get rate limit status for all resources', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'resources' => [
                'core' => [
                    'limit' => 5000,
                    'remaining' => 4999,
                    'reset' => 1640995200,
                    'used' => 1,
                ],
                'search' => [
                    'limit' => 30,
                    'remaining' => 30,
                    'reset' => 1640995200,
                    'used' => 0,
                ],
            ],
        ]));

        $rateLimits = $this->github->getRateLimitStatus();

        expect($rateLimits)->toHaveKey('core')
            ->and($rateLimits)->toHaveKey('search')
            ->and($rateLimits['core'])->toBeInstanceOf(RateLimitDTO::class)
            ->and($rateLimits['core']->limit)->toBe(5000)
            ->and($rateLimits['core']->remaining)->toBe(4999)
            ->and($rateLimits['core']->used)->toBe(1);
    });

    it('can get rate limit status for specific resource', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'resources' => [
                'core' => [
                    'limit' => 5000,
                    'remaining' => 4999,
                    'reset' => 1640995200,
                    'used' => 1,
                ],
            ],
        ]));

        $rateLimit = $this->github->getRateLimitForResource('core');

        expect($rateLimit)->toBeInstanceOf(RateLimitDTO::class)
            ->and($rateLimit->limit)->toBe(5000)
            ->and($rateLimit->remaining)->toBe(4999)
            ->and($rateLimit->resource)->toBe('core');
    });

    it('throws exception for non-existent resource', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'resources' => [
                'core' => [
                    'limit' => 5000,
                    'remaining' => 4999,
                    'reset' => 1640995200,
                    'used' => 1,
                ],
            ],
        ]));

        // Add another response for the second API call inside the exception
        $this->mockClient->addResponse(MockResponse::make([
            'resources' => [
                'core' => [
                    'limit' => 5000,
                    'remaining' => 4999,
                    'reset' => 1640995200,
                    'used' => 1,
                ],
            ],
        ]));

        expect(fn () => $this->github->getRateLimitForResource('nonexistent'))
            ->toThrow(ApiException::class);
    });

    it('can check if rate limit is exceeded', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'resources' => [
                'core' => [
                    'limit' => 5000,
                    'remaining' => 0,
                    'reset' => 1640995200,
                    'used' => 5000,
                ],
            ],
        ]));

        $hasExceeded = $this->github->hasRateLimitExceeded();

        expect($hasExceeded)->toBeTrue();
    });

    it('returns false when rate limit not exceeded', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'resources' => [
                'core' => [
                    'limit' => 5000,
                    'remaining' => 4999,
                    'reset' => 1640995200,
                    'used' => 1,
                ],
            ],
        ]));

        $hasExceeded = $this->github->hasRateLimitExceeded();

        expect($hasExceeded)->toBeFalse();
    });

    it('handles API errors gracefully', function () {
        $this->mockClient->addResponse(MockResponse::make([], 401));

        expect(fn () => $this->github->getRateLimitStatus())
            ->toThrow(ApiException::class);
    });
});

describe('RateLimitDTO', function () {
    it('can be created from API response', function () {
        $data = [
            'limit' => 5000,
            'remaining' => 4999,
            'reset' => 1640995200,
            'used' => 1,
        ];

        $dto = RateLimitDTO::fromApiResponse($data, 'core');

        expect($dto->limit)->toBe(5000)
            ->and($dto->remaining)->toBe(4999)
            ->and($dto->used)->toBe(1)
            ->and($dto->resource)->toBe('core');
    });

    it('can calculate usage percentage', function () {
        $data = [
            'limit' => 100,
            'remaining' => 25,
            'reset' => 1640995200,
            'used' => 75,
        ];

        $dto = RateLimitDTO::fromApiResponse($data);

        expect($dto->getUsagePercentage())->toBe(75.0);
    });

    it('can detect when approaching limit', function () {
        $data = [
            'limit' => 100,
            'remaining' => 10,
            'reset' => 1640995200,
            'used' => 90,
        ];

        $dto = RateLimitDTO::fromApiResponse($data);

        expect($dto->isApproachingLimit(80.0))->toBeTrue()
            ->and($dto->isApproachingLimit(95.0))->toBeFalse();
    });

    it('can detect when exceeded', function () {
        $exceededData = [
            'limit' => 100,
            'remaining' => 0,
            'reset' => 1640995200,
            'used' => 100,
        ];

        $notExceededData = [
            'limit' => 100,
            'remaining' => 50,
            'reset' => 1640995200,
            'used' => 50,
        ];

        $exceededDto = RateLimitDTO::fromApiResponse($exceededData);
        $notExceededDto = RateLimitDTO::fromApiResponse($notExceededData);

        expect($exceededDto->isExceeded())->toBeTrue()
            ->and($notExceededDto->isExceeded())->toBeFalse();
    });

    it('can convert to array', function () {
        $data = [
            'limit' => 5000,
            'remaining' => 4999,
            'reset' => 1640995200,
            'used' => 1,
        ];

        $dto = RateLimitDTO::fromApiResponse($data, 'core');
        $array = $dto->toArray();

        expect($array)->toHaveKey('limit')
            ->and($array)->toHaveKey('remaining')
            ->and($array)->toHaveKey('used')
            ->and($array)->toHaveKey('resource')
            ->and($array)->toHaveKey('usage_percentage')
            ->and($array)->toHaveKey('is_exceeded')
            ->and($array['resource'])->toBe('core');
    });
});
