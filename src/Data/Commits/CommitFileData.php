<?php

namespace JordanPartridge\GithubClient\Data\Commits;

class CommitFileData
{
    public function __construct(
        public string $filename,
        public string $status,
        public int $additions,
        public int $deletions,
        public int $changes,
        public string $blob_url,
        public string $raw_url,
        public string $contents_url,
        public ?string $patch = null,
        public ?string $sha = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filename: $data['filename'],
            status: $data['status'],
            additions: $data['additions'],
            deletions: $data['deletions'],
            changes: $data['changes'],
            blob_url: $data['blob_url'],
            raw_url: $data['raw_url'],
            contents_url: $data['contents_url'],
            patch: $data['patch'] ?? null,
            sha: $data['sha'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'status' => $this->status,
            'additions' => $this->additions,
            'deletions' => $this->deletions,
            'changes' => $this->changes,
            'blob_url' => $this->blob_url,
            'raw_url' => $this->raw_url,
            'contents_url' => $this->contents_url,
            'patch' => $this->patch,
            'sha' => $this->sha,
        ];
    }
}
