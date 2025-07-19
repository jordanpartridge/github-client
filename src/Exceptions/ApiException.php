<?php

namespace JordanPartridge\GithubClient\Exceptions;

use Saloon\Http\Response;

/**
 * Exception thrown when GitHub API returns an error response.
 */
class ApiException extends GithubClientException
{
    protected Response $response;
    protected array $errorDetails;

    public function __construct(
        Response $response,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        $this->response = $response;
        $this->errorDetails = $this->parseErrorResponse($response);

        if (empty($message)) {
            $message = $this->buildErrorMessage();
        }

        parent::__construct($message, $response->status(), $previous, [
            'status_code' => $response->status(),
            'response_body' => $response->body(),
            'error_details' => $this->errorDetails,
        ]);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    protected function parseErrorResponse(Response $response): array
    {
        $body = $response->json();
        
        return [
            'message' => $body['message'] ?? 'Unknown API error',
            'documentation_url' => $body['documentation_url'] ?? null,
            'errors' => $body['errors'] ?? [],
        ];
    }

    protected function buildErrorMessage(): string
    {
        $status = $this->response->status();
        $message = $this->errorDetails['message'];
        
        return "GitHub API error ({$status}): {$message}";
    }

    public static function notFound(string $resource, Response $response): self
    {
        return new self($response, "Resource not found: {$resource}");
    }

    public static function forbidden(Response $response, string $reason = ''): self
    {
        $message = 'Access forbidden';
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($response, $message);
    }
}