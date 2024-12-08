<?php

namespace JordanPartridge\GithubClient\Contracts;

use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
use JordanPartridge\GithubClient\Resources\RepoResource;

interface GithubConnectorInterface
{
    public function repos(): RepoResource;

    public function commits(): CommitResource;

    public function files(): FileResource;

    public function pullRequests(): PullRequestResource;
}
