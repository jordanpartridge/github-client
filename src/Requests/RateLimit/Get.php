<?php

namespace JordanPartridge\GithubClient\Requests\RateLimit;

use JordanPartridge\GithubClient\Data\RateLimitDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Get the current rate limit status for the authenticated user.
 */
class Get extends Request
{
    protected Method $method = Method::GET;

    /**
     * Get the API endpoint for this request.
     */
    public function resolveEndpoint(): string
    {
        return '/rate_limit';
    }

    /**
     * Convert the API response to rate limit DTOs.
     *
     * @param  Response  $response  The HTTP response from GitHub API
     * @return array<string, RateLimitDTO> Array of rate limit DTOs keyed by resource type
     */
    public function createDtoFromResponse(Response $response): array
    {
        $data = $response->json();
        $rateLimits = [];

        foreach ($data['resources'] as $resource => $limits) {
            $rateLimits[$resource] = RateLimitDTO::fromApiResponse($limits, $resource);
        }

        return $rateLimits;
    }
}
