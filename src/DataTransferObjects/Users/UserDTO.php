<?php

namespace JordanPartridge\GithubClient\DataTransferObjects\Users;

use JordanPartridge\GithubClient\DataTransferObjects\AbstractDTO;
use Saloon\Contracts\Response;

class UserDTO extends AbstractDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $login,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly string $avatarUrl,
        public readonly ?string $bio = null,
        public readonly ?string $company = null,
        public readonly ?string $location = null,
        public readonly ?string $blog = null,
        public readonly ?string $twitterUsername = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            login: $data['login'],
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            avatarUrl: $data['avatar_url'],
            bio: $data['bio'] ?? null,
            company: $data['company'] ?? null,
            location: $data['location'] ?? null,
            blog: $data['blog'] ?? null,
            twitterUsername: $data['twitter_username'] ?? null,
        );
    }

    public static function fromResponse(Response $response): self
    {
        return self::fromArray($response->json());
    }
}
