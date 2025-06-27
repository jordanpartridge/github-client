<?php

namespace JordanPartridge\GithubClient\Data\Commits;

class CommitStatsData
{
    public function __construct(
        public int $total,
        public int $additions,
        public int $deletions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'],
            additions: $data['additions'],
            deletions: $data['deletions'],
        );
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'additions' => $this->additions,
            'deletions' => $this->deletions,
        ];
    }
}
