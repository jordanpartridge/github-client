<?php

namespace JordanPartridge\GithubClient\Data\Issues;

use JordanPartridge\GithubClient\Data\GitUserData;

class IssueCommentDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $body,
        public readonly GitUserData $user,
        public readonly string $html_url,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'],
            body: $data['body'],
            user: GitUserData::fromArray($data['user']),
            html_url: $data['html_url'],
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'user' => $this->user->toArray(),
            'html_url' => $this->html_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
