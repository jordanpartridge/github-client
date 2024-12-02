<?php

namespace JordanPartridge\GithubClient\Data;

use Spatie\LaravelData\Data;

class FileDTO extends Data
{
    public function __construct(
        public string $sha,
        public string $filename,
        public string $status,
        public int $additions = 0,
        public int $deletions = 0,
        public int $changes = 0,
        public string $raw_url = '',
        public string $contents_url = '',
        public string $blob_url = '',
        public ?string $patch = null,
        public ?int $size = null,
    ) {}
}
