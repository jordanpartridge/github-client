<?php

namespace JordanPartridge\GithubClient\Exceptions;

/**
 * Exception thrown when network connectivity issues occur.
 */
class NetworkException extends GithubClientException
{
    protected string $operation;

    public function __construct(
        string $operation,
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->operation = $operation;

        $fullMessage = "Network error during {$operation}: {$message}";

        parent::__construct($fullMessage, $code, $previous, [
            'operation' => $operation,
            'original_message' => $message,
        ]);
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public static function timeout(string $operation, int $timeoutSeconds): self
    {
        return new self(
            $operation,
            "Request timed out after {$timeoutSeconds} seconds",
            408,
        );
    }

    public static function connectionFailed(string $operation, string $reason = ''): self
    {
        $message = 'Connection failed';
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($operation, $message, 503);
    }
}
