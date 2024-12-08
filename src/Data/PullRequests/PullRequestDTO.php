<?php

namespace JordanPartridge\GithubClient\Data\PullRequests;

use JordanPartridge\GithubClient\Data\GitUserData;
use Spatie\LaravelData\Data;

class PullRequestDTO extends Data
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
            merged_at: $data['merged_at'],
            merge_commit_sha: $data['merge_commit_sha'] ?? null,
            comments: $data['comments'],
            review_comments: $data['review_comments'],
            commits: $data['commits'],
            additions: $data['additions'],
            deletions: $data['deletions'],
            changed_files: $data['changed_files'],
            user: GitUserData::from($data['user']),
            merged_by: isset($data['merged_by']) ? GitUserData::from($data['merged_by']) : null,
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
            closed_at: $data['closed_at'],
        );
    }
}
