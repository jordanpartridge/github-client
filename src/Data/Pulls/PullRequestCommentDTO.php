<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

use JordanPartridge\GithubClient\Data\GitUserData;

class PullRequestCommentDTO
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
        public readonly ?CommentMetadata $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            node_id: $data['node_id'],
            path: $data['path'],
            position: $data['position'] ?? -1,
            original_position: $data['original_position'] ?? -1,
            commit_id: $data['commit_id'],
            original_commit_id: $data['original_commit_id'],
            user: GitUserData::fromArray($data['user']),
            body: $data['body'],
            html_url: $data['html_url'],
            pull_request_url: $data['pull_request_url'],
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
            metadata: CommentMetadata::extract(
                $data['body'],
                $data['path'],
                $data['position'] ?? null,
                $data['user']['login'] ?? null
            ),
        );
    }

    public static function fromApiResponse(array $data): self
    {
        return self::fromArray($data);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'node_id' => $this->node_id,
            'path' => $this->path,
            'position' => $this->position,
            'original_position' => $this->original_position,
            'commit_id' => $this->commit_id,
            'original_commit_id' => $this->original_commit_id,
            'user' => $this->user->toArray(),
            'body' => $this->body,
            'html_url' => $this->html_url,
            'pull_request_url' => $this->pull_request_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'metadata' => $this->metadata?->toArray(),
        ];
    }
}
