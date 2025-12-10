<?php

namespace JordanPartridge\GithubClient\Data\Installations;

use Carbon\Carbon;

class InstallationData
{
    public function __construct(
        public int $id,
        public string $account_login,
        public string $account_type,
        public ?string $target_type = null,
        public ?array $permissions = null,
        public ?array $events = null,
        public ?Carbon $created_at = null,
        public ?Carbon $updated_at = null,
        public ?string $app_slug = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            account_login: $data['account']['login'] ?? '',
            account_type: $data['account']['type'] ?? '',
            target_type: $data['target_type'] ?? null,
            permissions: $data['permissions'] ?? null,
            events: $data['events'] ?? null,
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            app_slug: $data['app_slug'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'account_login' => $this->account_login,
            'account_type' => $this->account_type,
            'target_type' => $this->target_type,
            'permissions' => $this->permissions,
            'events' => $this->events,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'app_slug' => $this->app_slug,
        ], fn ($value) => $value !== null);
    }
}
