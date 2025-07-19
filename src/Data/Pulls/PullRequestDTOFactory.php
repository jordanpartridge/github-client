<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

/**
 * Factory for creating appropriate PullRequest DTOs based on API response data.
 *
 * This factory intelligently detects whether the response data comes from:
 * - GitHub API list endpoint (returns PullRequestSummaryDTO)
 * - GitHub API individual endpoint (returns PullRequestDetailDTO)
 *
 * The detection is based on the presence of detailed fields like 'comments',
 * 'additions', and 'deletions' which are only included in individual PR responses.
 */
class PullRequestDTOFactory
{
    /**
     * Create the appropriate DTO based on the response data structure.
     *
     * @param  array  $data  Raw API response data
     */
    public static function fromResponse(array $data): PullRequestSummaryDTO|PullRequestDetailDTO
    {
        // Check if this response includes detailed fields
        if (self::hasDetailedFields($data)) {
            return PullRequestDetailDTO::fromDetailResponse($data);
        }

        return PullRequestSummaryDTO::fromListResponse($data);
    }

    /**
     * Create DTOs from an array of response data (for list endpoints).
     *
     * @param  array  $dataArray  Array of PR response data
     * @return array<PullRequestSummaryDTO|PullRequestDetailDTO>
     */
    public static function fromResponseArray(array $dataArray): array
    {
        return array_map(
            fn (array $data) => self::fromResponse($data),
            $dataArray
        );
    }

    /**
     * Force creation of a summary DTO (for list endpoints).
     *
     * Use this when you specifically want a summary DTO regardless of
     * the data structure, or when you know the data comes from a list endpoint.
     */
    public static function createSummary(array $data): PullRequestSummaryDTO
    {
        return PullRequestSummaryDTO::fromListResponse($data);
    }

    /**
     * Force creation of a detail DTO (for individual endpoints).
     *
     * Use this when you specifically want a detail DTO, or when you know
     * the data comes from an individual PR endpoint.
     */
    public static function createDetail(array $data): PullRequestDetailDTO
    {
        return PullRequestDetailDTO::fromDetailResponse($data);
    }

    /**
     * Check if the response data includes detailed fields.
     *
     * Detailed fields are only present in individual PR endpoint responses:
     * - comments: Number of issue comments
     * - additions: Lines of code added
     * - deletions: Lines of code deleted
     * - changed_files: Number of files changed
     */
    private static function hasDetailedFields(array $data): bool
    {
        // Check for multiple detailed fields to be confident this is a detail response
        $detailFields = ['comments', 'additions', 'deletions', 'changed_files'];
        $foundFields = 0;

        foreach ($detailFields as $field) {
            if (array_key_exists($field, $data)) {
                $foundFields++;
            }
        }

        // If we find at least 2 detail fields, treat as detailed response
        // This handles edge cases where some fields might be missing
        return $foundFields >= 2;
    }

    /**
     * Get information about what type of DTO would be created for given data.
     *
     * Useful for debugging or understanding API response structure.
     */
    public static function analyzeResponse(array $data): array
    {
        $hasDetails = self::hasDetailedFields($data);
        $availableFields = array_keys($data);
        $detailFields = ['comments', 'review_comments', 'additions', 'deletions', 'changed_files', 'commits'];

        return [
            'would_create' => $hasDetails ? 'PullRequestDetailDTO' : 'PullRequestSummaryDTO',
            'has_detailed_fields' => $hasDetails,
            'available_fields' => $availableFields,
            'detail_fields_present' => array_intersect($detailFields, $availableFields),
            'detail_fields_missing' => array_diff($detailFields, $availableFields),
        ];
    }
}
