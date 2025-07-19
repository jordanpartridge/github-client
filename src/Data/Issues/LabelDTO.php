<?php

namespace JordanPartridge\GithubClient\Data\Issues;

class LabelDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $color,
        public readonly ?string $description,
        public readonly bool $default,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            color: $data['color'],
            description: $data['description'] ?? null,
            default: $data['default'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
            'default' => $this->default,
        ];
    }
}
