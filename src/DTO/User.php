<?php

namespace JordanPartridge\GithubClient\DTO;

use Spatie\LaravelData\Data;

class User extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $login,
        public readonly ?string $node_id = null,
        public readonly string $avatar_url,
        public readonly string $url,
        public readonly string $type,
        public readonly bool $site_admin
    ) {}
}