<?php

namespace JordanPartridge\GithubClient\Data;

use DateTimeImmutable;

/**
 * Data Transfer Object for GitHub API rate limit information.
 */
class RateLimitDTO
{
    public function __construct(
        public readonly int $limit,
        public readonly int $remaining,
        public readonly DateTimeImmutable $reset,
        public readonly int $used,
        public readonly string $resource,
    ) {}

    /**
     * Create DTO from GitHub API response.
     */
    public static function fromApiResponse(array $data, string $resource = 'core'): self
    {
        return new self(
            limit: $data['limit'],
            remaining: $data['remaining'],
            reset: new DateTimeImmutable('@'.$data['reset']),
            used: $data['used'],
            resource: $resource,
        );
    }

    /**
     * Check if the rate limit has been exceeded.
     */
    public function isExceeded(): bool
    {
        return $this->remaining <= 0;
    }

    /**
     * Get seconds until rate limit resets.
     */
    public function getSecondsUntilReset(): int
    {
        return max(0, $this->reset->getTimestamp() - time());
    }

    /**
     * Get minutes until rate limit resets.
     */
    public function getMinutesUntilReset(): float
    {
        return round($this->getSecondsUntilReset() / 60, 1);
    }

    /**
     * Get percentage of rate limit used.
     */
    public function getUsagePercentage(): float
    {
        return round(($this->used / $this->limit) * 100, 1);
    }

    /**
     * Check if rate limit usage is approaching the limit (default 80%).
     */
    public function isApproachingLimit(float $threshold = 80.0): bool
    {
        return $this->getUsagePercentage() >= $threshold;
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'limit' => $this->limit,
            'remaining' => $this->remaining,
            'reset' => $this->reset->format('c'),
            'reset_timestamp' => $this->reset->getTimestamp(),
            'used' => $this->used,
            'resource' => $this->resource,
            'usage_percentage' => $this->getUsagePercentage(),
            'seconds_until_reset' => $this->getSecondsUntilReset(),
            'is_exceeded' => $this->isExceeded(),
            'is_approaching_limit' => $this->isApproachingLimit(),
        ];
    }
}
