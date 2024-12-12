<?php

namespace JordanPartridge\GithubClient\DTO\Commits;

use Spatie\LaravelData\Data;

class DetailedCommit extends Data
{
    public function __construct(
        public readonly string $message,
        public readonly array $author,
        public readonly array $committer,
        public readonly ?array $verification = null,
        public readonly ?string $url = null,
        public readonly ?int $comment_count = null,
    ) {}
}
