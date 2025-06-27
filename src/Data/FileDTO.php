<?php

namespace JordanPartridge\GithubClient\Data;

class FileDTO
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

    public static function fromArray(array $data): self
    {
        return new self(
            sha: $data['sha'],
            filename: $data['filename'],
            status: $data['status'],
            additions: $data['additions'] ?? 0,
            deletions: $data['deletions'] ?? 0,
            changes: $data['changes'] ?? 0,
            raw_url: $data['raw_url'] ?? '',
            contents_url: $data['contents_url'] ?? '',
            blob_url: $data['blob_url'] ?? '',
            patch: $data['patch'] ?? null,
            size: $data['size'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'sha' => $this->sha,
            'filename' => $this->filename,
            'status' => $this->status,
            'additions' => $this->additions,
            'deletions' => $this->deletions,
            'changes' => $this->changes,
            'raw_url' => $this->raw_url,
            'contents_url' => $this->contents_url,
            'blob_url' => $this->blob_url,
            'patch' => $this->patch,
            'size' => $this->size,
        ];
    }
}
