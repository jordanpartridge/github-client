<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

use JordanPartridge\GithubClient\Data\GitUserData;

/**
 * Pull Request Summary DTO for list endpoint responses.
 * 
 * This DTO represents the lightweight PR data returned by the GitHub API
 * list endpoint (/repos/owner/repo/pulls). It contains basic PR information
 * but does NOT include comment counts, additions/deletions, or other detailed
 * metrics that are only available in the individual PR endpoint.
 * 
 * Use this DTO when you need fast, lightweight PR listings without detailed stats.
 * For detailed PR data including comment counts, use PullRequestDetailDTO.
 */
class PullRequestSummaryDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $number,
        public readonly string $state,
        public readonly string $title,
        public readonly string $body,
        public readonly string $html_url,
        public readonly string $diff_url,
        public readonly string $patch_url,
        public readonly string $base_ref,
        public readonly string $head_ref,
        public readonly bool $draft,
        public readonly bool $merged,
        public readonly ?string $merged_at,
        public readonly ?string $merge_commit_sha,
        public readonly GitUserData $user,
        public readonly ?GitUserData $merged_by,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly ?string $closed_at,
    ) {}

    /**
     * Create DTO from GitHub API list endpoint response.
     * 
     * This method is optimized for the list endpoint response format,
     * which doesn't include detailed statistics like comment counts.
     */
    public static function fromListResponse(array $data): self
    {
        return new self(
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
        );
    }

    /**
     * Convert to array representation.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'state' => $this->state,
            'title' => $this->title,
            'body' => $this->body,
            'html_url' => $this->html_url,
            'diff_url' => $this->diff_url,
            'patch_url' => $this->patch_url,
            'base_ref' => $this->base_ref,
            'head_ref' => $this->head_ref,
            'draft' => $this->draft,
            'merged' => $this->merged,
            'merged_at' => $this->merged_at,
            'merge_commit_sha' => $this->merge_commit_sha,
            'user' => $this->user->toArray(),
            'merged_by' => $this->merged_by?->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->closed_at,
        ];
    }

    /**
     * Check if this PR has detailed data available.
     * 
     * Summary DTOs from list endpoints don't have detailed statistics.
     * To get detailed data, fetch the individual PR using PullRequestDetailDTO.
     */
    public function hasDetailedData(): bool
    {
        return false;
    }
}