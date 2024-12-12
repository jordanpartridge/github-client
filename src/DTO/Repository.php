<?php

namespace JordanPartridge\GithubClient\DTO;

use Spatie\LaravelData\Data;

class Repository extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $node_id,
        public readonly string $name,
        public readonly string $full_name,
        public readonly \User $owner,
        public readonly bool $private,
        public readonly ?string $description,
        public readonly bool $fork,
        public readonly ?string $language,
        public readonly string $default_branch,
        public readonly ?array $topics = null
    ) {}
}
