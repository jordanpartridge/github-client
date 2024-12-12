<?php

namespace JordanPartridge\GithubClient\DTO\Commits;

use Spatie\LaravelData\Data;

/**
 * Different GitHub endpoints return different commit data structures:
 * - List commits on a repository: Basic commit info
 * - Get a single commit: Detailed info with stats and files
 * - Commit in a PR: Additional merge-related fields
 */
class Commit extends Data
{
    public function __construct(
        // Always present fields
        public readonly string $sha,
        public readonly string $url,

        // Usually present but can be null
        public readonly ?string $node_id = null,
        public readonly ?array $author = null,
        public readonly ?array $committer = null,

        // Fields that vary by endpoint
        public readonly ?DetailedCommit $commit = null,
        public readonly ?array $parents = null,
        public readonly ?array $stats = null,
        public readonly ?array $files = null,

        // PR-specific fields
        public readonly ?string $merge_commit_sha = null,
        public readonly ?bool $merged = null,
        public readonly ?string $mergeable = null,
        public readonly ?int $comments = null,

        // GraphQL-specific fields
        public readonly ?string $oid = null,
        public readonly ?string $message_headline = null,
        public readonly ?string $message_body = null,
    ) {}
}
