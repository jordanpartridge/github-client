<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\PullRequests\PullRequestDTO;
use JordanPartridge\GithubClient\Data\PullRequests\PullRequestReviewDTO;
use JordanPartridge\GithubClient\Data\PullRequests\PullRequestCommentDTO;
use JordanPartridge\GithubClient\Enums\MergeMethod;

class PullRequestResource extends BaseResource
{
    public function all(string $owner, string $repo, array $parameters = []): array
    {
        $response = $this->client->get("/repos/{$owner}/{$repo}/pulls", $parameters);

        return array_map(
            fn (array $pullRequest) => PullRequestDTO::fromApiResponse($pullRequest),
            $response,
        );
    }

    public function get(string $owner, string $repo, int $number): PullRequestDTO
    {
        $response = $this->client->get("/repos/{$owner}/{$repo}/pulls/{$number}");

        return PullRequestDTO::fromApiResponse($response);
    }

    public function create(
        string $owner,
        string $repo,
        string $title,
        string $head,
        string $base,
        string $body = '',
        bool $draft = false,
    ): PullRequestDTO {
        $response = $this->client->post("/repos/{$owner}/{$repo}/pulls", [
            'title' => $title,
            'head' => $head,
            'base' => $base,
            'body' => $body,
            'draft' => $draft,
        ]);

        return PullRequestDTO::fromApiResponse($response);
    }

    public function update(
        string $owner,
        string $repo,
        int $number,
        array $parameters = [],
    ): PullRequestDTO {
        $response = $this->client->patch("/repos/{$owner}/{$repo}/pulls/{$number}", $parameters);

        return PullRequestDTO::fromApiResponse($response);
    }

    public function merge(
        string $owner,
        string $repo,
        int $number,
        ?string $commitMessage = null,
        ?string $sha = null,
        MergeMethod $mergeMethod = MergeMethod::Merge,
    ): bool {
        $parameters = array_filter([
            'commit_message' => $commitMessage,
            'sha' => $sha,
            'merge_method' => $mergeMethod->value,
        ]);

        $response = $this->client->put(
            "/repos/{$owner}/{$repo}/pulls/{$number}/merge",
            $parameters,
        );

        return $response['merged'] ?? false;
    }

    public function reviews(
        string $owner,
        string $repo,
        int $number,
    ): array {
        $response = $this->client->get("/repos/{$owner}/{$repo}/pulls/{$number}/reviews");

        return array_map(
            fn (array $review) => PullRequestReviewDTO::fromApiResponse($review),
            $response,
        );
    }

    public function createReview(
        string $owner,
        string $repo,
        int $number,
        string $body,
        string $event = 'COMMENT',
        array $comments = [],
    ): PullRequestReviewDTO {
        $response = $this->client->post(
            "/repos/{$owner}/{$repo}/pulls/{$number}/reviews",
            [
                'body' => $body,
                'event' => $event,
                'comments' => $comments,
            ],
        );

        return PullRequestReviewDTO::fromApiResponse($response);
    }

    public function comments(
        string $owner,
        string $repo,
        int $number,
    ): array {
        $response = $this->client->get("/repos/{$owner}/{$repo}/pulls/{$number}/comments");

        return array_map(
            fn (array $comment) => PullRequestCommentDTO::fromApiResponse($comment),
            $response,
        );
    }

    public function createComment(
        string $owner,
        string $repo,
        int $number,
        string $body,
        string $commitId,
        string $path,
        int $position,
    ): PullRequestCommentDTO {
        $response = $this->client->post(
            "/repos/{$owner}/{$repo}/pulls/{$number}/comments",
            [
                'body' => $body,
                'commit_id' => $commitId,
                'path' => $path,
                'position' => $position,
            ],
        );

        return PullRequestCommentDTO::fromApiResponse($response);
    }
}
