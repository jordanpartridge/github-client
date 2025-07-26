<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDetailDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestFileDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestReviewDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestSummaryDTO;
use JordanPartridge\GithubClient\Enums\MergeMethod;
use JordanPartridge\GithubClient\Requests\Pulls\Comments;
use JordanPartridge\GithubClient\Requests\Pulls\CommentsWithFilters;
use JordanPartridge\GithubClient\Requests\Pulls\Create;
use JordanPartridge\GithubClient\Requests\Pulls\CreateComment;
use JordanPartridge\GithubClient\Requests\Pulls\CreateReview;
use JordanPartridge\GithubClient\Requests\Pulls\DeleteComment;
use JordanPartridge\GithubClient\Requests\Pulls\Files;
use JordanPartridge\GithubClient\Requests\Pulls\Get;
use JordanPartridge\GithubClient\Requests\Pulls\GetComment;
use JordanPartridge\GithubClient\Requests\Pulls\GetWithDetailDTO;
use JordanPartridge\GithubClient\Requests\Pulls\Index;
use JordanPartridge\GithubClient\Requests\Pulls\IndexWithSummaryDTO;
use JordanPartridge\GithubClient\Requests\Pulls\Merge;
use JordanPartridge\GithubClient\Requests\Pulls\Reviews;
use JordanPartridge\GithubClient\Requests\Pulls\Update;
use JordanPartridge\GithubClient\Requests\Pulls\UpdateComment;

readonly class PullRequestResource extends BaseResource
{
    public function all(string $owner, string $repo, array $parameters = []): array
    {
        $response = $this->github()->connector()->send(new Index("{$owner}/{$repo}", $parameters));

        return $response->dto();
    }

    public function get(string $owner, string $repo, int $number): PullRequestDTO
    {
        $response = $this->github()->connector()->send(new Get("{$owner}/{$repo}", $number));

        return $response->dto();
    }

    public function create(
        string $owner,
        string $repo,
        string $title,
        string $head,
        string $base,
        string $body = '',
        bool $draft = false,
    ): PullRequestDTO {
        $response = $this->github()->connector()->send(new Create(
            "{$owner}/{$repo}",
            $title,
            $head,
            $base,
            $body,
            $draft
        ));

        return $response->dto();
    }

    public function update(
        string $owner,
        string $repo,
        int $number,
        array $parameters = [],
    ): PullRequestDTO {
        $response = $this->github()->connector()->send(new Update("{$owner}/{$repo}", $number, $parameters));

        return $response->dto();
    }

    public function merge(
        string $owner,
        string $repo,
        int $number,
        ?string $commitMessage = null,
        ?string $sha = null,
        MergeMethod $mergeMethod = MergeMethod::Merge,
    ): bool {
        $response = $this->github()->connector()->send(new Merge(
            "{$owner}/{$repo}",
            $number,
            $commitMessage,
            $sha,
            $mergeMethod
        ));

        $result = $response->dto();

        return $result->merged;
    }

    public function reviews(
        string $owner,
        string $repo,
        int $number,
    ): \Illuminate\Support\Collection {
        $response = $this->github()->connector()->send(new Reviews("{$owner}/{$repo}", $number));

        return $response->dto();
    }

    public function createReview(
        string $owner,
        string $repo,
        int $number,
        string $body,
        string $event = 'COMMENT',
        array $comments = [],
    ): PullRequestReviewDTO {
        $response = $this->github()->connector()->send(new CreateReview(
            "{$owner}/{$repo}",
            $number,
            $body,
            $event,
            $comments
        ));

        return $response->dto();
    }

    public function comments(
        string $owner,
        string $repo,
        int $number,
    ): array {
        $response = $this->github()->connector()->send(new Comments("{$owner}/{$repo}", $number));

        return $response->dto();
    }

    public function createComment(
        string $owner,
        string $repo,
        int $number,
        string $body,
        string $commitId,
        string $path,
        int $position,
    ): PullRequestCommentDTO {
        $response = $this->github()->connector()->send(new CreateComment(
            "{$owner}/{$repo}",
            $number,
            $body,
            $commitId,
            $path,
            $position
        ));

        return $response->dto();
    }

    /**
     * Get PR comments with filtering capabilities.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $number  Pull request number
     * @param  array  $filters  Filtering options
     * @return array<PullRequestCommentDTO>
     *
     * @example
     * // Get all CodeRabbit comments
     * $comments = $github->pullRequests()->commentsWithFilters('owner', 'repo', 42, [
     *     'author' => 'coderabbitai'
     * ]);
     *
     * // Get all bot comments with high severity
     * $comments = $github->pullRequests()->commentsWithFilters('owner', 'repo', 42, [
     *     'author_type' => 'bot',
     *     'severity' => 'high'
     * ]);
     *
     * // Get comments from specific file
     * $comments = $github->pullRequests()->commentsWithFilters('owner', 'repo', 42, [
     *     'file_path' => 'app/Http/Controllers/UserController.php'
     * ]);
     */
    public function commentsWithFilters(
        string $owner,
        string $repo,
        int $number,
        array $filters = [],
    ): array {
        $response = $this->github()->connector()->send(new CommentsWithFilters(
            "{$owner}/{$repo}",
            $number,
            $filters
        ));

        return $response->dto();
    }

    /**
     * Batch fetch all comments from a pull request with author filtering.
     * Alias for commentsWithFilters() for backward compatibility.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $number  Pull request number
     * @param  array  $filters  Filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function forPullRequest(
        string $owner,
        string $repo,
        int $number,
        array $filters = [],
    ): array {
        return $this->commentsWithFilters($owner, $repo, $number, $filters);
    }

    /**
     * Get a single pull request review comment by ID
     */
    public function getComment(
        string $owner,
        string $repo,
        int $commentId,
    ): PullRequestCommentDTO {
        $response = $this->github()->connector()->send(new GetComment(
            owner: $owner,
            repo: $repo,
            commentId: $commentId,
        ));

        return $response->dto();
    }

    /**
     * Update a pull request review comment
     */
    public function updateComment(
        string $owner,
        string $repo,
        int $commentId,
        string $body,
    ): PullRequestCommentDTO {
        $response = $this->github()->connector()->send(new UpdateComment(
            owner: $owner,
            repo: $repo,
            commentId: $commentId,
            bodyText: $body,
        ));

        return $response->dto();
    }

    /**
     * Delete a pull request review comment
     */
    public function deleteComment(
        string $owner,
        string $repo,
        int $commentId,
    ): bool {
        $response = $this->github()->connector()->send(new DeleteComment(
            owner: $owner,
            repo: $repo,
            commentId: $commentId,
        ));

        return $response->successful();
    }

    // === NEW DTO-SPECIFIC METHODS ===

    /**
     * Get PR summaries (lightweight, no comment counts) - RECOMMENDED for lists.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  array  $parameters  Query parameters
     * @return array<PullRequestSummaryDTO> Lightweight PR summaries
     */
    public function summaries(string $owner, string $repo, array $parameters = []): array
    {
        $response = $this->github()->connector()->send(new IndexWithSummaryDTO("{$owner}/{$repo}", $parameters));

        return $response->dto();
    }

    /**
     * Get PR with complete details including comment counts.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $number  Pull request number
     * @return PullRequestDetailDTO Complete PR data with statistics
     */
    public function detail(string $owner, string $repo, int $number): PullRequestDetailDTO
    {
        $response = $this->github()->connector()->send(new GetWithDetailDTO("{$owner}/{$repo}", $number));

        return $response->dto();
    }

    /**
     * Get multiple PRs with complete details (WARNING: Rate limit intensive).
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  array  $prNumbers  Array of PR numbers
     * @param  int  $maxRequests  Safety limit to prevent rate limit exhaustion
     * @return array<PullRequestDetailDTO> Complete PR data with statistics
     */
    public function detailsForMultiple(
        string $owner,
        string $repo,
        array $prNumbers,
        int $maxRequests = 20
    ): array {
        // Safety limit to prevent rate limit issues
        $prNumbers = array_slice($prNumbers, 0, $maxRequests);

        $details = [];
        foreach ($prNumbers as $number) {
            try {
                $details[] = $this->detail($owner, $repo, $number);
            } catch (\Exception $e) {
                // Skip PRs that can't be fetched
                continue;
            }
        }

        return $details;
    }

    /**
     * Get recent PRs with complete details (optimized for common workflow).
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $limit  Number of recent PRs (max 10 for rate limit protection)
     * @param  string  $state  PR state filter
     * @return array<PullRequestDetailDTO> Recent PRs with complete data
     */
    public function recentDetails(
        string $owner,
        string $repo,
        int $limit = 5,
        string $state = 'open'
    ): array {
        // First get PR list
        $summaries = $this->summaries($owner, $repo, [
            'state' => $state,
            'sort' => 'updated',
            'direction' => 'desc',
            'per_page' => min($limit, 10), // Protect against rate limit abuse
        ]);

        // Get detailed data for each
        $prNumbers = array_map(fn ($pr) => $pr->number, $summaries);

        return $this->detailsForMultiple($owner, $repo, $prNumbers, $limit);
    }

    // === PR DIFF ANALYSIS METHODS ===

    /**
     * Get all files changed in a pull request with diff statistics.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $number  Pull request number
     * @return array<PullRequestFileDTO> Array of file changes with diff data
     *
     * @example
     * // Get all changed files
     * $files = $github->pullRequests()->files('owner', 'repo', 42);
     *
     * // Analyze file changes
     * foreach ($files as $file) {
     *     echo "{$file->filename}: +{$file->additions}/-{$file->deletions}\n";
     *     echo "File type: {$file->getFileType()}\n";
     *     echo "Tags: " . implode(', ', $file->getAnalysisTags()) . "\n";
     * }
     */
    public function files(string $owner, string $repo, int $number): array
    {
        $response = $this->github()->connector()->send(new Files("{$owner}/{$repo}", $number));

        return $response->dto();
    }

    /**
     * Get diff analysis for a pull request with categorized file changes.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $number  Pull request number
     * @return array Analysis data with categorized files and statistics
     *
     * @example
     * $analysis = $github->pullRequests()->diff('owner', 'repo', 42);
     *
     * echo "Total files: {$analysis['summary']['total_files']}\n";
     * echo "Large changes: {$analysis['summary']['large_changes']}\n";
     * echo "Test files: " . count($analysis['categories']['tests']) . "\n";
     * echo "Config files: " . count($analysis['categories']['config']) . "\n";
     */
    public function diff(string $owner, string $repo, int $number): array
    {
        $files = $this->files($owner, $repo, $number);

        $categories = [
            'tests' => [],
            'config' => [],
            'docs' => [],
            'code' => [],
            'other' => [],
        ];

        $summary = [
            'total_files' => count($files),
            'total_additions' => 0,
            'total_deletions' => 0,
            'total_changes' => 0,
            'large_changes' => 0,
            'new_files' => 0,
            'deleted_files' => 0,
            'modified_files' => 0,
            'renamed_files' => 0,
        ];

        foreach ($files as $file) {
            // Update summary statistics
            $summary['total_additions'] += $file->additions;
            $summary['total_deletions'] += $file->deletions;
            $summary['total_changes'] += $file->changes;

            if ($file->isLargeChange()) {
                $summary['large_changes']++;
            }
            if ($file->isAdded()) {
                $summary['new_files']++;
            }
            if ($file->isDeleted()) {
                $summary['deleted_files']++;
            }
            if ($file->isModified()) {
                $summary['modified_files']++;
            }
            if ($file->isRenamed()) {
                $summary['renamed_files']++;
            }

            // Categorize files
            if ($file->isTestFile()) {
                $categories['tests'][] = $file;
            } elseif ($file->isConfigFile()) {
                $categories['config'][] = $file;
            } elseif ($file->isDocumentationFile()) {
                $categories['docs'][] = $file;
            } elseif (in_array($file->getFileType(), ['php', 'javascript', 'typescript', 'python', 'java', 'go', 'rust', 'c', 'cpp'])) {
                $categories['code'][] = $file;
            } else {
                $categories['other'][] = $file;
            }
        }

        return [
            'summary' => $summary,
            'categories' => $categories,
            'files' => $files,
            'analysis_tags' => $this->extractAnalysisTags($files),
        ];
    }

    /**
     * Extract unique analysis tags from all files for AI assessment.
     */
    private function extractAnalysisTags(array $files): array
    {
        $allTags = [];
        foreach ($files as $file) {
            $allTags = array_merge($allTags, $file->getAnalysisTags());
        }

        return array_unique($allTags);
    }
}
