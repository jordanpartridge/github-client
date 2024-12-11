<?php

namespace JordanPartridge\GithubClient\ValueObjects;

use InvalidArgumentException;

readonly class Repo
{
    private function __construct(
        private string $owner,
        private string $name,
    ) {}

    public static function fromFullName(string $full_name): self
    {
        [$owner, $name] = self::validateAndParseRepoName($full_name);

        return new self($owner, $name);
    }

    private static function validateAndParseRepoName(string $full_name): array
    {
        /**
         * if the `$full_name` doesn't contain a slash, I send it back
         */
        $parts = explode('/', $full_name);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Repository must be in format "owner/repo".');
        }

        [$owner, $name] = $parts;

        /**
         * If either are empty, that's a no-go fo sho
         */
        if (empty($owner) || empty($name)) {
            throw new InvalidArgumentException('Owner and repo name cannot be empty.');
        }

        /**
         * While were at it, lets regex the parts to make sure they're valid
         */
        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $owner) || ! preg_match('/^[a-zA-Z0-9._-]+$/', $name)) {
            throw new InvalidArgumentException("Invalid characters in repository name '{$full_name}'.");
        }

        return [$owner, $name];
    }

    public static function fromRepo(Repo $repo): self
    {
        return new self($repo->owner, $repo->name);
    }

    public function owner(): string
    {
        return $this->owner;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function fullName(): string
    {
        return "{$this->owner}/{$this->name}";
    }
}
