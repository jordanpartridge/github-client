<?php

namespace JordanPartridge\GithubClient\Concerns;

use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use Saloon\Http\Response;

/**
 * Trait for handling GitHub Issues API responses.
 *
 * This trait provides common functionality for processing issue list responses
 * and filtering out pull requests (which are returned by GitHub's Issues API).
 */
trait HandlesIssueResponses
{
    /**
     * Convert GitHub API response to an array of IssueDTO objects.
     *
     * Filters out pull requests since GitHub's Issues API returns both issues and PRs.
     * Also handles invalid or incomplete data gracefully by skipping problematic items.
     *
     * @param  Response  $response  The HTTP response from GitHub Issues API
     * @return IssueDTO[] Array of issue data transfer objects
     */
    public function createDtoFromResponse(Response $response): array
    {
        $issues = [];
        foreach ($response->json() as $item) {
            // Skip pull requests - GitHub's Issues API returns both
            if (! isset($item['pull_request'])) {
                try {
                    $issues[] = IssueDTO::fromApiResponse($item);
                } catch (\InvalidArgumentException $e) {
                    // Skip items with invalid or incomplete data
                    continue;
                }
            }
        }

        return $issues;
    }
}
