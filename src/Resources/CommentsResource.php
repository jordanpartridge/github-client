<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;
use JordanPartridge\GithubClient\Requests\Pulls\CommentsWithFilters;

/**
 * Resource for GitHub comment operations with filtering capabilities.
 */
readonly class CommentsResource extends BaseResource
{
    /**
     * Get all comments from a PR with filtering capabilities.
     *
     * @param  int  $prNumber  Pull request number
     * @param  array  $filters  Filtering options
     * @return array<PullRequestCommentDTO>
     *
     * @example
     * // Get all CodeRabbit comments from PR
     * $comments = Github::comments()->forPullRequest(42, [
     *     'author' => 'coderabbitai',
     *     'include_line_data' => true
     * ]);
     *
     * // Get all bot comments
     * $botComments = Github::comments()->forPullRequest(42, [
     *     'author_type' => 'bot'
     * ]);
     *
     * // Get high severity security issues
     * $securityIssues = Github::comments()->forPullRequest(42, [
     *     'severity' => 'high',
     *     'claim_type' => 'security'
     * ]);
     */
    public function forPullRequest(int $prNumber, array $filters = []): array
    {
        // Default repository from config or current context
        $ownerRepo = $this->resolveRepository($filters);

        $response = $this->github()->connector()->send(new CommentsWithFilters(
            $ownerRepo,
            $prNumber,
            $filters,
        ));

        return $response->dto();
    }

    /**
     * Get comments filtered by author.
     *
     * @param  int  $prNumber  Pull request number
     * @param  string  $author  Author username to filter by
     * @param  array  $additionalFilters  Additional filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function byAuthor(int $prNumber, string $author, array $additionalFilters = []): array
    {
        return $this->forPullRequest($prNumber, array_merge($additionalFilters, [
            'author' => $author,
        ]));
    }

    /**
     * Get comments filtered by author type (bot or human).
     *
     * @param  int  $prNumber  Pull request number
     * @param  string  $authorType  'bot' or 'human'
     * @param  array  $additionalFilters  Additional filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function byAuthorType(int $prNumber, string $authorType, array $additionalFilters = []): array
    {
        return $this->forPullRequest($prNumber, array_merge($additionalFilters, [
            'author_type' => $authorType,
        ]));
    }

    /**
     * Get comments filtered by severity level.
     *
     * @param  int  $prNumber  Pull request number
     * @param  string  $severity  'high', 'medium', or 'low'
     * @param  array  $additionalFilters  Additional filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function bySeverity(int $prNumber, string $severity, array $additionalFilters = []): array
    {
        return $this->forPullRequest($prNumber, array_merge($additionalFilters, [
            'severity' => $severity,
        ]));
    }

    /**
     * Get comments for a specific file.
     *
     * @param  int  $prNumber  Pull request number
     * @param  string  $filePath  File path to filter by
     * @param  array  $additionalFilters  Additional filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function forFile(int $prNumber, string $filePath, array $additionalFilters = []): array
    {
        return $this->forPullRequest($prNumber, array_merge($additionalFilters, [
            'file_path' => $filePath,
        ]));
    }

    /**
     * Get CodeRabbit AI comments.
     *
     * @param  int  $prNumber  Pull request number
     * @param  array  $additionalFilters  Additional filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function codeRabbit(int $prNumber, array $additionalFilters = []): array
    {
        return $this->byAuthor($prNumber, 'coderabbitai', $additionalFilters);
    }

    /**
     * Get all bot comments (AI reviewers, GitHub Actions, etc.).
     *
     * @param  int  $prNumber  Pull request number
     * @param  array  $additionalFilters  Additional filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function bots(int $prNumber, array $additionalFilters = []): array
    {
        return $this->byAuthorType($prNumber, 'bot', $additionalFilters);
    }

    /**
     * Get human reviewer comments only.
     *
     * @param  int  $prNumber  Pull request number
     * @param  array  $additionalFilters  Additional filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function humans(int $prNumber, array $additionalFilters = []): array
    {
        return $this->byAuthorType($prNumber, 'human', $additionalFilters);
    }

    /**
     * Resolve repository from filters or use configured default.
     */
    private function resolveRepository(array $filters): string
    {
        if (isset($filters['repository'])) {
            return $filters['repository'];
        }

        // You could add logic here to get repository from:
        // - Configuration
        // - Current git context
        // - Environment variables
        // For now, throw exception to force explicit repository
        throw new \InvalidArgumentException(
            'Repository must be specified in filters as "repository" => "owner/repo"',
        );
    }
}
