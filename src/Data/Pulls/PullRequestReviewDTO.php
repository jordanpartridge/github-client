<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

use JordanPartridge\GithubClient\Data\GitUserData;

class PullRequestReviewDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $node_id,
        public readonly GitUserData $user,
        public readonly string $body,
        public readonly string $state,
        public readonly string $html_url,
        public readonly string $pull_request_url,
        public readonly string $commit_id,
        public readonly string $submitted_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            node_id: $data['node_id'],
            user: GitUserData::fromArray($data['user']),
            body: $data['body'] ?? '',
            state: $data['state'],
            html_url: $data['html_url'],
            pull_request_url: $data['pull_request_url'],
            commit_id: $data['commit_id'],
            submitted_at: $data['submitted_at'],
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
            'user' => $this->user->toArray(),
            'body' => $this->body,
            'state' => $this->state,
            'html_url' => $this->html_url,
            'pull_request_url' => $this->pull_request_url,
            'commit_id' => $this->commit_id,
            'submitted_at' => $this->submitted_at,
        ];
    }
}
