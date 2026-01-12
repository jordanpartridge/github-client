<?php

use JordanPartridge\GithubClient\Data\RateLimitDTO;

it('can create RateLimitDTO from API response', function () {
    $resetTime = time() + 3600;
    $data = [
        'limit' => 5000,
        'remaining' => 4500,
        'reset' => $resetTime,
        'used' => 500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data, 'core');

    expect($rateLimit->limit)->toBe(5000);
    expect($rateLimit->remaining)->toBe(4500);
    expect($rateLimit->reset->getTimestamp())->toBe($resetTime);
    expect($rateLimit->used)->toBe(500);
    expect($rateLimit->resource)->toBe('core');
});

it('can convert RateLimitDTO to array', function () {
    $resetTime = time() + 3600;
    $data = [
        'limit' => 5000,
        'remaining' => 4500,
        'reset' => $resetTime,
        'used' => 500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data, 'core');
    $array = $rateLimit->toArray();

    expect($array['limit'])->toBe(5000);
    expect($array['remaining'])->toBe(4500);
    expect($array['used'])->toBe(500);
    expect($array['resource'])->toBe('core');
    expect($array['reset_timestamp'])->toBe($resetTime);
    expect($array)->toHaveKey('reset');
    expect($array)->toHaveKey('usage_percentage');
    expect($array)->toHaveKey('seconds_until_reset');
    expect($array)->toHaveKey('is_exceeded');
    expect($array)->toHaveKey('is_approaching_limit');
});

it('detects exceeded rate limit', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 0,
        'reset' => time() + 3600,
        'used' => 5000,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->isExceeded())->toBeTrue();
});

it('detects not exceeded rate limit', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 1000,
        'reset' => time() + 3600,
        'used' => 4000,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->isExceeded())->toBeFalse();
});

it('calculates seconds until reset', function () {
    $resetTime = time() + 3600;
    $data = [
        'limit' => 5000,
        'remaining' => 4500,
        'reset' => $resetTime,
        'used' => 500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->getSecondsUntilReset())->toBeGreaterThanOrEqual(3595);
    expect($rateLimit->getSecondsUntilReset())->toBeLessThanOrEqual(3600);
});

it('returns zero for past reset time', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 4500,
        'reset' => time() - 100,
        'used' => 500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->getSecondsUntilReset())->toBe(0);
});

it('calculates minutes until reset', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 4500,
        'reset' => time() + 3600,
        'used' => 500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->getMinutesUntilReset())->toBeGreaterThanOrEqual(59.0);
    expect($rateLimit->getMinutesUntilReset())->toBeLessThanOrEqual(60.0);
});

it('calculates usage percentage', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 2500,
        'reset' => time() + 3600,
        'used' => 2500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->getUsagePercentage())->toBe(50.0);
});

it('detects approaching limit at default threshold', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 500,
        'reset' => time() + 3600,
        'used' => 4500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->isApproachingLimit())->toBeTrue();
});

it('detects not approaching limit', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 3000,
        'reset' => time() + 3600,
        'used' => 2000,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->isApproachingLimit())->toBeFalse();
});

it('allows custom threshold for approaching limit', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 2500,
        'reset' => time() + 3600,
        'used' => 2500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->isApproachingLimit(50.0))->toBeTrue();
    expect($rateLimit->isApproachingLimit(60.0))->toBeFalse();
});

it('handles different resource types', function () {
    $resources = ['core', 'search', 'graphql', 'integration_manifest', 'code_scanning_upload'];

    foreach ($resources as $resource) {
        $data = [
            'limit' => 5000,
            'remaining' => 4500,
            'reset' => time() + 3600,
            'used' => 500,
        ];

        $rateLimit = RateLimitDTO::fromApiResponse($data, $resource);
        expect($rateLimit->resource)->toBe($resource);
    }
});

it('uses core as default resource', function () {
    $data = [
        'limit' => 5000,
        'remaining' => 4500,
        'reset' => time() + 3600,
        'used' => 500,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data);

    expect($rateLimit->resource)->toBe('core');
});

it('handles search rate limit with lower limits', function () {
    $data = [
        'limit' => 30,
        'remaining' => 10,
        'reset' => time() + 60,
        'used' => 20,
    ];

    $rateLimit = RateLimitDTO::fromApiResponse($data, 'search');

    expect($rateLimit->limit)->toBe(30);
    expect($rateLimit->resource)->toBe('search');
    expect($rateLimit->getUsagePercentage())->toBeGreaterThan(66.0);
});
