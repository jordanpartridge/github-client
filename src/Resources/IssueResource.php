<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Issue;
use JordanPartridge\GithubClient\Requests\Issues\CreateIssueRequest;
use JordanPartridge\GithubClient\Requests\Issues\GetIssueRequest;
use JordanPartridge\GithubClient\Requests\Issues\ListIssuesRequest;
use JordanPartridge\GithubClient\Requests\Issues\UpdateIssueRequest;

readonly class IssueResource extends BaseResource
{
    /**
     * List issues for a repository
     *
     * @param  string  $repository  Repository in format "owner/repo"
     * @param  array  $filters  Optional filters (state, labels, sort, direction, since)
     * @return array<Issue>
     */
    public function all(string $repository, array $filters = []): array
    {
        $response = $this->connector->send(new ListIssuesRequest($repository, $filters));

        return array_map(
            fn (array $issue) => Issue::fromApiResponse($issue),
            $response->json()
        );
    }

    /**
     * Get a specific issue
     *
     * @param  string  $repository  Repository in format "owner/repo"
     * @param  int  $number  Issue number
     */
    public function get(string $repository, int $number): Issue
    {
        $response = $this->connector->send(new GetIssueRequest($repository, $number));

        return Issue::fromApiResponse($response->json());
    }

    /**
     * Create a new issue
     *
     * @param  string  $repository  Repository in format "owner/repo"
     * @param  string  $title  Issue title
     * @param  string  $body  Issue body
     * @param  array  $options  Additional options (labels, assignees, milestone)
     */
    public function create(string $repository, string $title, string $body, array $options = []): Issue
    {
        $response = $this->connector->send(new CreateIssueRequest(
            $repository,
            $title,
            $body,
            $options
        ));

        return Issue::fromApiResponse($response->json());
    }

    /**
     * Update an existing issue
     *
     * @param  string  $repository  Repository in format "owner/repo"
     * @param  int  $number  Issue number
     * @param  array  $data  Update data (title, body, state, labels, assignees, milestone)
     */
    public function update(string $repository, int $number, array $data): Issue
    {
        $response = $this->connector->send(new UpdateIssueRequest(
            $repository,
            $number,
            $data
        ));

        return Issue::fromApiResponse($response->json());
    }
}
