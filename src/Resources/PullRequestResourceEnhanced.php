<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\Requests\Pulls\Get;
use JordanPartridge\GithubClient\Requests\Pulls\Index;

/**
 * Enhanced PullRequestResource with methods to fetch PRs with complete data including comment counts.
 */
readonly class PullRequestResourceEnhanced extends PullRequestResource
{
    /**
     * Get all pull requests with complete data including comment counts.
     *
     * WARNING: This makes multiple API calls (one per PR) and will consume more rate limit.
     * Use sparingly and consider pagination limits.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  array  $parameters  Query parameters for filtering
     * @param  int  $maxPRs  Maximum number of PRs to fetch detailed data for (default: 10)
     * @return array<PullRequestDTO> PRs with complete data including comment counts
     */
    public function allWithCommentCounts(
        string $owner,
        string $repo,
        array $parameters = [],
        int $maxPRs = 10,
    ): array {
        // First get the list of PRs (without comment counts)
        $listResponse = $this->github()->connector()->send(new Index("{$owner}/{$repo}", $parameters));
        $prList = $listResponse->dto();

        // Limit the number of detailed fetches to avoid rate limit issues
        $prList = array_slice($prList, 0, $maxPRs);

        // Fetch detailed data for each PR (includes comment counts)
        $detailedPRs = [];
        foreach ($prList as $pr) {
            $detailResponse = $this->github()->connector()->send(new Get("{$owner}/{$repo}", $pr->number));
            $detailedPRs[] = $detailResponse->dto();
        }

        return $detailedPRs;
    }

    /**
     * Get pull requests with comment counts for a specific list of PR numbers.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  array  $prNumbers  Array of PR numbers to fetch
     * @return array<PullRequestDTO> PRs with complete data including comment counts
     */
    public function getMultipleWithCommentCounts(
        string $owner,
        string $repo,
        array $prNumbers,
    ): array {
        $detailedPRs = [];

        foreach ($prNumbers as $number) {
            try {
                $response = $this->github()->connector()->send(new Get("{$owner}/{$repo}", $number));
                $detailedPRs[] = $response->dto();
            } catch (\Exception $e) {
                // Skip PRs that can't be fetched (might be deleted, private, etc.)
                continue;
            }
        }

        return $detailedPRs;
    }

    /**
     * Get recent PRs with comment counts (optimized for common use case).
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $limit  Number of recent PRs to fetch (default: 5, max: 20)
     * @param  string  $state  PR state: 'open', 'closed', 'all' (default: 'open')
     * @return array<PullRequestDTO> Recent PRs with complete data including comment counts
     */
    public function recentWithCommentCounts(
        string $owner,
        string $repo,
        int $limit = 5,
        string $state = 'open',
    ): array {
        // Ensure reasonable limits to avoid rate limit issues
        $limit = min($limit, 20);

        return $this->allWithCommentCounts($owner, $repo, [
            'state' => $state,
            'sort' => 'updated',
            'direction' => 'desc',
            'per_page' => $limit,
        ], $limit);
    }
}
