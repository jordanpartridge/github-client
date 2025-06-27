<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use Carbon\Carbon;

class CommitAuthorData
{
    public function __construct(
        public string $name,
        public string $email,
        public Carbon $date,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            date: Carbon::parse($data['date']),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'date' => $this->date->toISOString(),
        ];
    }
}
