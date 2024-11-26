<?php

namespace JordanPartridge\GithubClient\Data;

class FileDTO
{
    public function __construct(
        private string $sha,
        private string $filename,
        private string $status,
        private int    $additions = 0,
        private int    $deletions = 0,
        private int    $changes = 0,
        private string $raw_url = '',
        private string $contents_url = '',
        private string $blob_url = '',
        private string $patch = '',
        private int    $size = 0,
    )
    {
    }

    public function getSha(): string
    {
        return $this->sha;
    }

    public function getAdditions(): int
    {
        return $this->additions;
    }

    public function getChanges(): int
    {
        return $this->changes;
    }

    public function getDeletions(): int
    {
        return $this->deletions;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public static function fromArray(array $data): array
    {
        return array_map(function (array $item) {
            // Ensure all required fields are present with defaults
            return new FileDTO(
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
            );
        }, $data);
    }

    public function getContentsUrl(): string
    {
        return $this->contents_url;
    }

    public function getRawUrl(): string
    {
        return $this->raw_url;
    }
}
