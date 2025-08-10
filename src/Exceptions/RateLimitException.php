<?php

namespace JordanPartridge\GithubClient\Exceptions;

use DateTimeImmutable;

/**
 * Exception thrown when GitHub API rate limit is exceeded.
 */
class RateLimitException extends GithubClientException
{
    protected int $remainingRequests;

    protected DateTimeImmutable $resetTime;

    protected int $totalLimit;

    public function __construct(
        int $remainingRequests,
        DateTimeImmutable $resetTime,
        int $totalLimit,
        string $message = '',
        int $code = 429,
        ?\Throwable $previous = null,
    ) {
        $this->remainingRequests = $remainingRequests;
        $this->resetTime = $resetTime;
        $this->totalLimit = $totalLimit;

        if (empty($message)) {
            $message = sprintf(
                'GitHub API rate limit exceeded. %d/%d requests remaining. Reset at %s',
                $remainingRequests,
                $totalLimit,
                $resetTime->format('Y-m-d H:i:s T'),
            );
        }

        parent::__construct($message, $code, $previous, [
            'remaining_requests' => $remainingRequests,
            'reset_time' => $resetTime->format('c'),
            'total_limit' => $totalLimit,
            'seconds_until_reset' => $resetTime->getTimestamp() - time(),
        ]);
    }

    public function getRemainingRequests(): int
    {
        return $this->remainingRequests;
    }

    public function getResetTime(): DateTimeImmutable
    {
        return $this->resetTime;
    }

    public function getTotalLimit(): int
    {
        return $this->totalLimit;
    }

    public function getSecondsUntilReset(): int
    {
        return max(0, $this->resetTime->getTimestamp() - time());
    }
}
