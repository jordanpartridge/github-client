<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

class MergeResponseDTO
{
    public function __construct(
        public readonly bool $merged,
        public readonly string $sha,
        public readonly string $message,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            merged: $data['merged'] ?? false,
            sha: $data['sha'] ?? '',
            message: $data['message'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'merged' => $this->merged,
            'sha' => $this->sha,
            'message' => $this->message,
        ];
    }
}