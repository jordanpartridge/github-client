<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

use JordanPartridge\GithubClient\Data\GitUserData;

/**
 * Pull Request Detail DTO for individual endpoint responses.
 *
 * This DTO represents the complete PR data returned by the GitHub API
 * individual endpoint (/repos/owner/repo/pulls/NUMBER). It contains all
 * PR information including comment counts, code statistics, and other
 * detailed metrics that are only available in individual PR responses.
 *
 * Use this DTO when you need complete PR data including accurate comment
 * counts and code change statistics.
 */
class PullRequestDetailDTO extends PullRequestSummaryDTO
{
    public function __construct(
        // Inherit all summary fields
        int $id,
        int $number,
        string $state,
        string $title,
        string $body,
        string $html_url,
        string $diff_url,
        string $patch_url,
        string $base_ref,
        string $head_ref,
        bool $draft,
        bool $merged,
        ?string $merged_at,
        ?string $merge_commit_sha,
        GitUserData $user,
        ?GitUserData $merged_by,
        string $created_at,
        string $updated_at,
        ?string $closed_at,

        // Additional detailed fields only available in individual endpoint
        public readonly int $comments,
        public readonly int $review_comments,
        public readonly int $commits,
        public readonly int $additions,
        public readonly int $deletions,
        public readonly int $changed_files,

        // Merge status fields
        public readonly ?bool $mergeable,
        public readonly ?string $mergeable_state,
        public readonly ?bool $rebaseable,
    ) {
        parent::__construct(
            $id,
            $number,
            $state,
            $title,
            $body,
            $html_url,
            $diff_url,
            $patch_url,
            $base_ref,
            $head_ref,
            $draft,
            $merged,
            $merged_at,
            $merge_commit_sha,
            $user,
            $merged_by,
            $created_at,
            $updated_at,
            $closed_at,
        );
    }

    /**
     * Create DTO from GitHub API individual endpoint response.
     *
     * This method is optimized for the individual endpoint response format,
     * which includes detailed statistics like comment counts and code changes.
     */
    public static function fromDetailResponse(array $data): self
    {
        return new self(
            // Summary fields
            id: $data['id'],
            number: $data['number'],
            state: $data['state'],
            title: $data['title'],
            body: $data['body'] ?? '',
            html_url: $data['html_url'],
            diff_url: $data['diff_url'],
            patch_url: $data['patch_url'],
            base_ref: $data['base']['ref'],
            head_ref: $data['head']['ref'],
            draft: $data['draft'] ?? false,
            merged: $data['merged'] ?? false,
            merged_at: $data['merged_at'] ?? null,
            merge_commit_sha: $data['merge_commit_sha'] ?? null,
            user: GitUserData::fromArray($data['user']),
            merged_by: isset($data['merged_by']) ? GitUserData::fromArray($data['merged_by']) : null,
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
            closed_at: $data['closed_at'] ?? null,

            // Detail fields with proper type coercion
            comments: (int) ($data['comments'] ?? 0),
            review_comments: (int) ($data['review_comments'] ?? 0),
            commits: (int) ($data['commits'] ?? 0),
            additions: (int) ($data['additions'] ?? 0),
            deletions: (int) ($data['deletions'] ?? 0),
            changed_files: (int) ($data['changed_files'] ?? 0),

            // Merge status fields
            mergeable: $data['mergeable'] ?? null,
            mergeable_state: $data['mergeable_state'] ?? null,
            rebaseable: $data['rebaseable'] ?? null,
        );
    }

    /**
     * Convert to array representation including detailed fields.
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'comments' => $this->comments,
            'review_comments' => $this->review_comments,
            'commits' => $this->commits,
            'additions' => $this->additions,
            'deletions' => $this->deletions,
            'changed_files' => $this->changed_files,
            'mergeable' => $this->mergeable,
            'mergeable_state' => $this->mergeable_state,
            'rebaseable' => $this->rebaseable,
        ]);
    }

    /**
     * Check if this PR has detailed data available.
     *
     * Detail DTOs always have complete statistics.
     */
    public function hasDetailedData(): bool
    {
        return true;
    }

    /**
     * Get the total lines of code changed (additions + deletions).
     */
    public function getTotalLinesChanged(): int
    {
        return $this->additions + $this->deletions;
    }

    /**
     * Get the ratio of additions to total changes.
     */
    public function getAdditionRatio(): float
    {
        $total = $this->getTotalLinesChanged();

        return $total > 0 ? $this->additions / $total : 0.0;
    }

    /**
     * Check if this PR has comments or reviews.
     */
    public function hasComments(): bool
    {
        return $this->comments > 0 || $this->review_comments > 0;
    }

    /**
     * Get total comment count (both regular comments and review comments).
     */
    public function getTotalComments(): int
    {
        return $this->comments + $this->review_comments;
    }

    /**
     * Check if this PR is ready to merge (no conflicts).
     */
    public function isReadyToMerge(): bool
    {
        return $this->mergeable === true && $this->mergeable_state === 'clean';
    }

    /**
     * Check if this PR has merge conflicts.
     */
    public function hasMergeConflicts(): bool
    {
        return $this->mergeable === false || $this->mergeable_state === 'dirty';
    }

    /**
     * Check if this PR can be rebased onto the target branch.
     */
    public function canRebase(): bool
    {
        return $this->rebaseable === true;
    }

    /**
     * Get a human-readable merge status description.
     */
    public function getMergeStatusDescription(): string
    {
        if ($this->mergeable === null) {
            return 'Merge status unknown (checking...)';
        }

        return match ($this->mergeable_state) {
            'clean' => 'Ready to merge',
            'dirty' => 'Has merge conflicts',
            'unstable' => 'Mergeable with failing checks',
            'blocked' => 'Blocked by branch protection',
            'behind' => 'Behind base branch',
            'draft' => 'Draft pull request',
            default => $this->mergeable_state ?? 'Unknown status',
        };
    }

    /**
     * Create a summary representation for display purposes.
     */
    public function getSummary(): array
    {
        return [
            'pr' => "#{$this->number}: {$this->title}",
            'stats' => [
                'comments' => $this->comments,
                'review_comments' => $this->review_comments,
                'commits' => $this->commits,
                'changes' => "+{$this->additions}/-{$this->deletions}",
                'files' => $this->changed_files,
            ],
            'merge_status' => [
                'mergeable' => $this->mergeable,
                'mergeable_state' => $this->mergeable_state,
                'rebaseable' => $this->rebaseable,
                'description' => $this->getMergeStatusDescription(),
            ],
            'state' => $this->state,
            'author' => $this->user->login,
        ];
    }
}
