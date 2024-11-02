<?php

namespace JordanPartridge\GithubClient\Concerns;

trait ValidatesRepoName
{

    public function validateRepoName($repoName): void
    {
        if (!preg_match('/^[a-zA-Z0-9-_]+$/', $repoName)) {
            throw new \InvalidArgumentException('Invalid repository name. Only alphanumeric characters, dashes, and underscores are allowed.');
        }
    }

}
