<?php

namespace JordanPartridge\GithubClient\DataTransferObjects\Repositories;

use JordanPartridge\GithubClient\DataTransferObjects\AbstractDTO;
use Saloon\Contracts\Response;

class RepositoryDTO extends AbstractDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $fullName,
        public readonly ?string $description,
        public readonly bool $private,
        public readonly ?string $language,
        public readonly string $defaultBranch,
        public readonly ?int $stargazersCount = 0,
        public readonly ?int $forksCount = 0,
        public readonly ?string $url = null,
        public readonly ?string $htmlUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            fullName: $data['full_name'],
            description: $data['description'] ?? null,
            private: $data['private'] ?? false,
            language: $data['language'] ?? null,
            defaultBranch: $data['default_branch'] ?? 'main',
            stargazersCount: $data['stargazers_count'] ?? 0,
            forksCount: $data['forks_count'] ?? 0,
            url: $data['url'] ?? null,
            htmlUrl: $data['html_url'] ?? null,
        );
    }

    public static function fromResponse(Response $response): self
    {
        return self::fromArray($response->json());
    }
}