<?php

namespace JordanPartridge\GithubClient\Concerns;

use InvalidArgumentException;

trait ValidatesRepoName
{
    private const MAX_OWNER_LENGTH = 39;

    private const MAX_REPOSITORY_LENGTH = 100;

    public function validateRepoName(string $repoPath): void
    {
        $parts = explode('/', $repoPath);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Repository path must be in the format "owner/repository".');
        }

        [$owner, $repository] = $parts;

        // Validate owner length
        if (strlen($owner) > self::MAX_OWNER_LENGTH) {
            throw new InvalidArgumentException('Owner name cannot exceed 39 characters.');
        }

        // Validate repository length
        if (strlen($repository) > self::MAX_REPOSITORY_LENGTH) {
            throw new InvalidArgumentException('Repository name cannot exceed 100 characters.');
        }

        // Validate owner name
        if (! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/', $owner)) {
            throw new InvalidArgumentException(
                'Invalid owner name. Owner names must:' . PHP_EOL .
                '- Start and end with an alphanumeric character' . PHP_EOL .
                '- Contain only alphanumeric characters or hyphens',
            );
        }

        // Validate repository name
        if (! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]$/', $repository)) {
            throw new InvalidArgumentException(
                'Invalid repository name. Repository names must:' . PHP_EOL .
                '- Start and end with an alphanumeric character' . PHP_EOL .
                '- Contain only alphanumeric characters, dots, dashes, or underscores',
            );
        }

        if (str_contains($repository, '..')) {
            throw new InvalidArgumentException('Repository name cannot contain consecutive dots.');
        }
    }
}
