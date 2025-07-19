<?php

namespace JordanPartridge\GithubClient\Exceptions;

use Exception;

/**
 * Base exception for all GitHub Client related errors.
 */
abstract class GithubClientException extends Exception
{
    protected array $context = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context information about the exception.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Add context information to the exception.
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;

        return $this;
    }
}
