<?php

namespace JordanPartridge\GithubClient\Data\PullRequests;

use JordanPartridge\GithubClient\Data\GitUserData;
use Spatie\LaravelData\Data;

class PullRequestCommentDTO extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $node_id,
        public readonly string $path,
        public readonly int $position,
        public readonly int $original_position,
        public readonly string $commit_id,
        public readonly string $original_commit_id,
        public readonly GitUserData $user,
        public readonly string $body,
        public readonly string $html_url,
        public readonly string $pull_request_url,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'],
            node_id: $data['node_id'],
            path: $data['path'],
            position: $data['position'] ?? -1,
            original_position: $data['original_position'] ?? -1,
            commit_id: $data['commit_id'],
            original_commit_id: $data['original_commit_id'],
            user: GitUserData::from($data['user']),
            body: $data['body'],
            html_url: $data['html_url'],
            pull_request_url: $data['pull_request_url'],
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
        );
    }
}
