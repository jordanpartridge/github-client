<?php

namespace JordanPartridge\GithubClient\DataTransferObjects\Commits;

use JordanPartridge\GithubClient\DataTransferObjects\AbstractDTO;
use Saloon\Contracts\Response;

class CommitDTO extends AbstractDTO
{
    public function __construct(
        public readonly string $sha,
        public readonly string $message,
        public readonly array $author,
        public readonly array $committer,
        public readonly string $url,
        public readonly ?array $stats = null,
        public readonly ?array $files = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sha: $data['sha'],
            message: $data['commit']['message'],
            author: $data['commit']['author'],
            committer: $data['commit']['committer'],
            url: $data['url'],
            stats: $data['stats'] ?? null,
            files: $data['files'] ?? null,
        );
    }

    public static function fromResponse(Response $response): self
    {
        return self::fromArray($response->json());
    }
}