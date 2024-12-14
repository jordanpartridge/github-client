<?php

namespace JordanPartridge\GithubClient\ValueObjects;

class Repo
{
    private function __construct(
        private string $owner,
        private string $name
    ) {}

    public static function from(string $fullName): self
    {
        $parts = explode('/', $fullName);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Repository name must be in format "owner/name"');
        }

        return new self($parts[0], $parts[1]);
    }

    public function toString(): string
    {
        return "{$this->owner}/{$this->name}";
    }

    // Added this method to match what Get.php is expecting
    public function fullName(): string
    {
        return $this->toString();
    }

    public function owner(): string
    {
        return $this->owner;
    }

    public function name(): string
    {
        return $this->name;
    }
}
