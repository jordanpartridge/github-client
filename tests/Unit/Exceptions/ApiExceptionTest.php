<?php

use JordanPartridge\GithubClient\Exceptions\ApiException;
use JordanPartridge\GithubClient\Exceptions\GithubClientException;
use Saloon\Http\Response;
use Mockery;

describe('ApiException', function () {
    beforeEach(function () {
        // Helper to create a mock Response
        $this->createMockResponse = function (array $body, int $status = 400): Response {
            $mockResponse = Mockery::mock(Response::class);
            $mockResponse->shouldReceive('status')->andReturn($status);
            $mockResponse->shouldReceive('json')->andReturn($body);
            $mockResponse->shouldReceive('body')->andReturn(json_encode($body));

            return $mockResponse;
        };
    });

    afterEach(function () {
        Mockery::close();
    });

    describe('constructor', function () {
        it('sets response', function () {
            $response = ($this->createMockResponse)(['message' => 'Bad Request'], 400);
            $exception = new ApiException($response);

            expect($exception->getResponse())->toBe($response);
        });

        it('parses error details from response body', function () {
            $response = ($this->createMockResponse)([
                'message' => 'Validation Failed',
                'documentation_url' => 'https://docs.github.com/rest',
                'errors' => [
                    ['resource' => 'Issue', 'code' => 'missing', 'field' => 'title'],
                ],
            ], 422);
            $exception = new ApiException($response);

            $details = $exception->getErrorDetails();
            expect($details['message'])->toBe('Validation Failed')
                ->and($details['documentation_url'])->toBe('https://docs.github.com/rest')
                ->and($details['errors'])->toHaveCount(1);
        });

        it('uses status code as exception code', function () {
            $response = ($this->createMockResponse)(['message' => 'Not Found'], 404);
            $exception = new ApiException($response);

            expect($exception->getCode())->toBe(404);
        });

        it('generates message from response when not provided', function () {
            $response = ($this->createMockResponse)(['message' => 'Not Found'], 404);
            $exception = new ApiException($response);

            expect($exception->getMessage())->toBe('GitHub API error (404): Not Found');
        });

        it('uses provided message when given', function () {
            $response = ($this->createMockResponse)(['message' => 'Not Found'], 404);
            $exception = new ApiException($response, 'Custom error message');

            expect($exception->getMessage())->toBe('Custom error message');
        });

        it('handles response without message field', function () {
            $response = ($this->createMockResponse)(['error' => 'Unknown'], 500);
            $exception = new ApiException($response);

            $details = $exception->getErrorDetails();
            expect($details['message'])->toBe('Unknown API error');
        });

        it('handles response without documentation_url', function () {
            $response = ($this->createMockResponse)(['message' => 'Error'], 500);
            $exception = new ApiException($response);

            $details = $exception->getErrorDetails();
            expect($details['documentation_url'])->toBeNull();
        });

        it('handles response without errors array', function () {
            $response = ($this->createMockResponse)(['message' => 'Error'], 500);
            $exception = new ApiException($response);

            $details = $exception->getErrorDetails();
            expect($details['errors'])->toBe([]);
        });

        it('includes response details in context', function () {
            $response = ($this->createMockResponse)(['message' => 'Bad Request'], 400);
            $exception = new ApiException($response);

            $context = $exception->getContext();
            expect($context)->toHaveKey('status_code')
                ->and($context)->toHaveKey('response_body')
                ->and($context)->toHaveKey('error_details')
                ->and($context['status_code'])->toBe(400);
        });

        it('accepts previous exception', function () {
            $previous = new Exception('Original');
            $response = ($this->createMockResponse)(['message' => 'Error'], 500);
            $exception = new ApiException($response, '', $previous);

            expect($exception->getPrevious())->toBe($previous);
        });
    });

    describe('getResponse', function () {
        it('returns the original response', function () {
            $response = ($this->createMockResponse)(['message' => 'Error'], 500);
            $exception = new ApiException($response);

            expect($exception->getResponse())->toBe($response);
        });
    });

    describe('getErrorDetails', function () {
        it('returns parsed error details', function () {
            $response = ($this->createMockResponse)([
                'message' => 'Problems parsing JSON',
                'documentation_url' => 'https://docs.github.com/rest/overview/resources-in-the-rest-api#client-errors',
            ], 400);
            $exception = new ApiException($response);

            $details = $exception->getErrorDetails();
            expect($details)->toHaveKey('message')
                ->and($details)->toHaveKey('documentation_url')
                ->and($details)->toHaveKey('errors');
        });
    });

    describe('notFound', function () {
        it('creates not found exception with resource name', function () {
            $response = ($this->createMockResponse)(['message' => 'Not Found'], 404);
            $exception = ApiException::notFound('repository owner/repo', $response);

            expect($exception->getMessage())->toBe('Resource not found: repository owner/repo')
                ->and($exception->getCode())->toBe(404);
        });

        it('preserves response in not found exception', function () {
            $response = ($this->createMockResponse)(['message' => 'Not Found'], 404);
            $exception = ApiException::notFound('issue #123', $response);

            expect($exception->getResponse())->toBe($response);
        });
    });

    describe('forbidden', function () {
        it('creates forbidden exception without reason', function () {
            $response = ($this->createMockResponse)(['message' => 'Forbidden'], 403);
            $exception = ApiException::forbidden($response);

            expect($exception->getMessage())->toBe('Access forbidden')
                ->and($exception->getCode())->toBe(403);
        });

        it('creates forbidden exception with reason', function () {
            $response = ($this->createMockResponse)(['message' => 'Forbidden'], 403);
            $exception = ApiException::forbidden($response, 'Rate limit exceeded');

            expect($exception->getMessage())->toBe('Access forbidden: Rate limit exceeded');
        });

        it('preserves response in forbidden exception', function () {
            $response = ($this->createMockResponse)(['message' => 'Forbidden'], 403);
            $exception = ApiException::forbidden($response);

            expect($exception->getResponse())->toBe($response);
        });
    });

    describe('common API error scenarios', function () {
        it('handles 401 Unauthorized', function () {
            $response = ($this->createMockResponse)([
                'message' => 'Requires authentication',
                'documentation_url' => 'https://docs.github.com/rest/overview/resources-in-the-rest-api#authentication',
            ], 401);
            $exception = new ApiException($response);

            expect($exception->getCode())->toBe(401)
                ->and($exception->getMessage())->toContain('Requires authentication');
        });

        it('handles 403 Forbidden with rate limit', function () {
            $response = ($this->createMockResponse)([
                'message' => 'API rate limit exceeded',
                'documentation_url' => 'https://docs.github.com/rest/overview/resources-in-the-rest-api#rate-limiting',
            ], 403);
            $exception = new ApiException($response);

            expect($exception->getCode())->toBe(403)
                ->and($exception->getMessage())->toContain('rate limit');
        });

        it('handles 422 Unprocessable Entity', function () {
            $response = ($this->createMockResponse)([
                'message' => 'Validation Failed',
                'errors' => [
                    ['resource' => 'Issue', 'code' => 'missing_field', 'field' => 'title'],
                ],
            ], 422);
            $exception = new ApiException($response);

            expect($exception->getCode())->toBe(422)
                ->and($exception->getErrorDetails()['errors'])->toHaveCount(1);
        });

        it('handles 500 Internal Server Error', function () {
            $response = ($this->createMockResponse)([
                'message' => 'Server Error',
            ], 500);
            $exception = new ApiException($response);

            expect($exception->getCode())->toBe(500);
        });

        it('handles 502 Bad Gateway', function () {
            $response = ($this->createMockResponse)([
                'message' => 'Bad Gateway',
            ], 502);
            $exception = new ApiException($response);

            expect($exception->getCode())->toBe(502);
        });

        it('handles 503 Service Unavailable', function () {
            $response = ($this->createMockResponse)([
                'message' => 'Service Unavailable',
            ], 503);
            $exception = new ApiException($response);

            expect($exception->getCode())->toBe(503);
        });
    });

    describe('inheritance', function () {
        it('extends GithubClientException', function () {
            $response = ($this->createMockResponse)(['message' => 'Error'], 500);
            $exception = new ApiException($response);

            expect($exception)->toBeInstanceOf(GithubClientException::class);
        });

        it('inherits addContext functionality', function () {
            $response = ($this->createMockResponse)(['message' => 'Error'], 500);
            $exception = new ApiException($response);
            $exception->addContext('request_id', 'abc123');

            $context = $exception->getContext();
            expect($context)->toHaveKey('request_id')
                ->and($context['request_id'])->toBe('abc123');
        });
    });
});
