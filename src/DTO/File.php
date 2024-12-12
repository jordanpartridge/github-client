<?php

namespace JordanPartridge\GithubClient\DTO;

use Spatie\LaravelData\Data;

class File extends Data
{
    public function __construct(
        public readonly string $sha,
        public readonly string $filename,
        public readonly string $status,
        public readonly int $additions,
        public readonly int $deletions,
        public readonly int $changes,
        public readonly ?string $patch = null
    ) {}
}