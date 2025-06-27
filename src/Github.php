<?php

namespace JordanPartridge\GithubClient;

use ConduitUi\GitHubConnector\GithubConnector;
use JordanPartridge\GithubClient\Resources\ActionsResource;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\IssueResource;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
use JordanPartridge\GithubClient\Resources\RepoResource;

class Github
{
    use Concerns\ValidatesRepoName;

    public function __construct(
        protected GithubConnector $connector,
    ) {}

    public function connector(): GithubConnector
    {
        return $this->connector;
    }

    public function repos(): RepoResource
    {
        return new RepoResource($this);
    }

    public function commits(): CommitResource
    {
        return new CommitResource($this);
    }

    public function files(): FileResource
    {
        return new FileResource($this);
    }

    public function pullRequests(): PullRequestResource
    {
        return new PullRequestResource($this);
    }

    public function actions(): ActionsResource
    {
        return new ActionsResource($this);
    }

    public function issues(): IssueResource
    {
        return new IssueResource($this);
    }

    // HTTP methods delegated to connector
    public function get(string $url, array $parameters = []): array
    {
        return $this->connector->get($url, $parameters);
    }

    public function post(string $url, array $parameters = []): array
    {
        return $this->connector->post($url, $parameters);
    }

    public function patch(string $url, array $parameters = []): array
    {
        return $this->connector->patch($url, $parameters);
    }

    public function put(string $url, array $parameters = []): array
    {
        return $this->connector->put($url, $parameters);
    }

    public function delete(string $url, array $parameters = []): array
    {
        return $this->connector->delete($url, $parameters);
    }
}
