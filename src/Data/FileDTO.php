<?php

namespace JordanPartridge\GithubClient\Data;

readonly class FileDTO
{
    public function __construct(
        public string $sha,
        public string $filename,
        public string $status,
        public int    $additions = 0,
        public int    $deletions = 0,
        public int    $changes = 0,
        public string $raw_url = '',
        public string $contents_url = '',
        public string $blob_url = '',
        public string $patch = '',
        public int    $size = 0,
    ) {}

    public static function fromArray(array $data): array
    {
        return array_map(fn(array $item) => new self(
            sha: $item['sha'] ?? '',
            filename: $item['filename'] ?? '',
            status: $item['status'] ?? '',
            additions: $item['additions'] ?? 0,
            deletions: $item['deletions'] ?? 0,
            changes: $item['changes'] ?? 0,
            raw_url: $item['raw_url'] ?? '',
            contents_url: $item['contents_url'] ?? '',
            blob_url: $item['blob_url'] ?? '',
            patch: $item['patch'] ?? '',
            size: $item['size'] ?? 0
        ), $data);
    }
}
