<?php

namespace JordanPartridge\GithubClient\DataTransferObjects\PullRequests;

use JordanPartridge\GithubClient\DataTransferObjects\AbstractDTO;
use Saloon\Contracts\Response;

class PullRequestDTO extends AbstractDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $number,
        public readonly string $title,
        public readonly ?string $body,
        public readonly string $state,
        public readonly bool $draft,
        public readonly bool $merged,
        public readonly string $baseRef,
        public readonly string $headRef,
        public readonly array $user,
        public readonly ?string $mergedAt = null,
        public readonly ?string $closedAt = null,
        public readonly ?array $labels = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            number: $data['number'],
            title: $data['title'],
            body: $data['body'] ?? null,
            state: $data['state'],
            draft: $data['draft'] ?? false,
            merged: $data['merged'] ?? false,
            baseRef: $data['base']['ref'],
            headRef: $data['head']['ref'],
            user: $data['user'],
            mergedAt: $data['merged_at'] ?? null,
            closedAt: $data['closed_at'] ?? null,
            labels: $data['labels'] ?? null,
        );
    }

    public static function fromResponse(Response $response): self
    {
        return self::fromArray($response->json());
    }
}
