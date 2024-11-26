<?php

namespace JordanPartridge\GithubClient\ValueObjects;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;

readonly class Repo
{
    use ValidatesRepoName;
    private function __construct(
        private string $owner,
        private string $name
    ) {
        $this->validateRepoName($this->fullName());
    }

    public static function fromFullName(string $fullName): self
    {
        [$owner, $name] = explode('/', $fullName);

        if (empty($owner) || empty($name)) {
            throw new InvalidArgumentException('Repository must be in format "owner/repo"');
        }

        return new self($owner, $name);
    }

    public static function fromRepo(Repo $repo): self
    {
        return self::fromFullName($repo->fullName());
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
