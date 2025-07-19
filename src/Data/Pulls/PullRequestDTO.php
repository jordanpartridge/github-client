<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

use JordanPartridge\GithubClient\Data\GitUserData;

class PullRequestDTO
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
        public readonly int $comments,
        public readonly int $review_comments,
        public readonly int $commits,
        public readonly int $additions,
        public readonly int $deletions,
        public readonly int $changed_files,
        public readonly GitUserData $user,
        public readonly ?GitUserData $merged_by,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly ?string $closed_at,
    ) {}

    public static function fromApiResponse(array $data): self
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
            comments: (int) ($data['comments'] ?? 0),
            review_comments: (int) ($data['review_comments'] ?? 0),
            commits: (int) ($data['commits'] ?? 0),
            additions: (int) ($data['additions'] ?? 0),
            deletions: (int) ($data['deletions'] ?? 0),
            changed_files: (int) ($data['changed_files'] ?? 0),
            user: GitUserData::fromArray($data['user']),
            merged_by: isset($data['merged_by']) ? GitUserData::fromArray($data['merged_by']) : null,
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
            closed_at: $data['closed_at'] ?? null,
        );
    }

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
            'comments' => $this->comments,
            'review_comments' => $this->review_comments,
            'commits' => $this->commits,
            'additions' => $this->additions,
            'deletions' => $this->deletions,
            'changed_files' => $this->changed_files,
            'user' => $this->user->toArray(),
            'merged_by' => $this->merged_by?->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->closed_at,
        ];
    }
}
