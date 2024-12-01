<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use Spatie\LaravelData\Data;

class CommitFileData extends Data
{
    public function __construct(
        public string $sha,
        public string $filename,
        public string $status,
        public int $additions,
        public int $deletions,
        public int $changes,
        public string $blob_url,
        public string $raw_url,
        public string $contents_url,
        public string $patch,
    ) {}
}

