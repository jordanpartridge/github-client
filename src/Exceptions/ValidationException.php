<?php

namespace JordanPartridge\GithubClient\Exceptions;

/**
 * Exception thrown when validation fails for request parameters.
 */
class ValidationException extends GithubClientException
{
    protected string $field;

    protected mixed $value;

    public function __construct(
        string $field,
        mixed $value,
        string $message,
        int $code = 422,
        ?\Throwable $previous = null,
    ) {
        $this->field = $field;
        $this->value = $value;

        $fullMessage = "Validation failed for field '{$field}': {$message}";

        parent::__construct($fullMessage, $code, $previous, [
            'field' => $field,
            'value' => $value,
            'validation_message' => $message,
        ]);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
