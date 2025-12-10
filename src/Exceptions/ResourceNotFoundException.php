<?php

namespace JordanPartridge\GithubClient\Exceptions;

use Saloon\Http\Response;

/**
 * Exception thrown when a requested GitHub resource cannot be found (404).
 */
class ResourceNotFoundException extends ApiException
{
    public function __construct(
        string $message,
        Response $response,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($response, $message, $previous);
    }

    public static function fromResponse(Response $response, string $resourceType = 'Resource'): self
    {
        $data = $response->json();
        $message = $data['message'] ?? "{$resourceType} not found";

        return new self($message, $response);
    }
}
