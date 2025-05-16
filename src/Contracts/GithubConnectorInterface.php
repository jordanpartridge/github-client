<?php

namespace JordanPartridge\GithubClient\Contracts;

use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
use JordanPartridge\GithubClient\Resources\RepoResource;
use Saloon\Http\Request;
use Saloon\Http\Response;

interface GithubConnectorInterface
{
    // Resource getters
    public function repos(): RepoResource;

    public function commits(): CommitResource;

    public function files(): FileResource;

    public function pullRequests(): PullRequestResource;

    // HTTP methods
    public function send(Request $request): Response;

    public function get(string $url, array $parameters = []): array;

    public function post(string $url, array $parameters = []): array;

    public function patch(string $url, array $parameters = []): array;

    public function put(string $url, array $parameters = []): array;

    public function delete(string $url, array $parameters = []): array;
}
