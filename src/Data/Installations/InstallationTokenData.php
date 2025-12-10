<?php

namespace JordanPartridge\GithubClient\Data\Installations;

use Carbon\Carbon;

class InstallationTokenData
{
    public function __construct(
        public string $token,
        public Carbon $expires_at,
        public ?array $permissions = null,
        public ?string $repository_selection = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'],
            expires_at: Carbon::parse($data['expires_at']),
            permissions: $data['permissions'] ?? null,
            repository_selection: $data['repository_selection'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'token' => $this->token,
            'expires_at' => $this->expires_at->toISOString(),
            'permissions' => $this->permissions,
            'repository_selection' => $this->repository_selection,
        ], fn ($value) => $value !== null);
    }

    public function isExpired(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->expires_at);
    }

    public function expiresIn(): int
    {
        return Carbon::now()->diffInSeconds($this->expires_at, false);
    }
}
