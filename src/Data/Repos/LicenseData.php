<?php

namespace JordanPartridge\GithubClient\Data\Repos;

class LicenseData
{
    public function __construct(
        public string $key,
        public string $name,
        public string $spdx_id,
        public ?string $url,
        public string $node_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            name: $data['name'],
            spdx_id: $data['spdx_id'],
            url: $data['url'] ?? null,
            node_id: $data['node_id'],
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'spdx_id' => $this->spdx_id,
            'url' => $this->url,
            'node_id' => $this->node_id,
        ];
    }
}
