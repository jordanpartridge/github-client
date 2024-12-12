<?php

namespace JordanPartridge\GithubClient\DTO;

use Spatie\LaravelData\Data;

class Issue extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $node_id,
        public readonly int $number,
        public readonly string $title,
        public readonly \User $user,
        public readonly string $state,
        public readonly bool $locked,
        public readonly ?\User $assignee = null,
        public readonly array $assignees,
        public readonly int $comments,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly ?string $closed_at = null
    ) {}
}
