<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestReviewDTO;
use JordanPartridge\GithubClient\Enums\MergeMethod;
use JordanPartridge\GithubClient\Requests\Pulls\Comments;
use JordanPartridge\GithubClient\Requests\Pulls\CommentsWithFilters;
use JordanPartridge\GithubClient\Requests\Pulls\Create;
use JordanPartridge\GithubClient\Requests\Pulls\CreateComment;
use JordanPartridge\GithubClient\Requests\Pulls\CreateReview;
use JordanPartridge\GithubClient\Requests\Pulls\Get;
use JordanPartridge\GithubClient\Requests\Pulls\Index;
use JordanPartridge\GithubClient\Requests\Pulls\Merge;
use JordanPartridge\GithubClient\Requests\Pulls\Reviews;
use JordanPartridge\GithubClient\Requests\Pulls\Update;

readonly class PullRequestResource extends BaseResource
{
    public function all(string $owner, string $repo, array $parameters = []): array
    {
        $response = $this->github()->connector()->send(new Index("{$owner}/{$repo}", $parameters));

        return $response->dto();
    }

    public function get(string $owner, string $repo, int $number): PullRequestDTO
    {
        $response = $this->github()->connector()->send(new Get("{$owner}/{$repo}", $number));

        return $response->dto();
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
        $response = $this->github()->connector()->send(new Create(
            "{$owner}/{$repo}",
            $title,
            $head,
            $base,
            $body,
            $draft
        ));

        return $response->dto();
    }

    public function update(
        string $owner,
        string $repo,
        int $number,
        array $parameters = [],
    ): PullRequestDTO {
        $response = $this->github()->connector()->send(new Update("{$owner}/{$repo}", $number, $parameters));

        return $response->dto();
    }

    public function merge(
        string $owner,
        string $repo,
        int $number,
        ?string $commitMessage = null,
        ?string $sha = null,
        MergeMethod $mergeMethod = MergeMethod::Merge,
    ): bool {
        $response = $this->github()->connector()->send(new Merge(
            "{$owner}/{$repo}",
            $number,
            $commitMessage,
            $sha,
            $mergeMethod
        ));

        $result = $response->dto();

        return $result->merged;
    }

    public function reviews(
        string $owner,
        string $repo,
        int $number,
    ): array {
        $response = $this->github()->connector()->send(new Reviews("{$owner}/{$repo}", $number));

        return $response->dto();
    }

    public function createReview(
        string $owner,
        string $repo,
        int $number,
        string $body,
        string $event = 'COMMENT',
        array $comments = [],
    ): PullRequestReviewDTO {
        $response = $this->github()->connector()->send(new CreateReview(
            "{$owner}/{$repo}",
            $number,
            $body,
            $event,
            $comments
        ));

        return $response->dto();
    }

    public function comments(
        string $owner,
        string $repo,
        int $number,
    ): array {
        $response = $this->github()->connector()->send(new Comments("{$owner}/{$repo}", $number));

        return $response->dto();
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
        $response = $this->github()->connector()->send(new CreateComment(
            "{$owner}/{$repo}",
            $number,
            $body,
            $commitId,
            $path,
            $position
        ));

        return $response->dto();
    }

    /**
     * Get PR comments with filtering capabilities.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $number  Pull request number
     * @param  array  $filters  Filtering options
     * @return array<PullRequestCommentDTO>
     *
     * @example
     * // Get all CodeRabbit comments
     * $comments = $github->pullRequests()->commentsWithFilters('owner', 'repo', 42, [
     *     'author' => 'coderabbitai'
     * ]);
     *
     * // Get all bot comments with high severity
     * $comments = $github->pullRequests()->commentsWithFilters('owner', 'repo', 42, [
     *     'author_type' => 'bot',
     *     'severity' => 'high'
     * ]);
     *
     * // Get comments from specific file
     * $comments = $github->pullRequests()->commentsWithFilters('owner', 'repo', 42, [
     *     'file_path' => 'app/Http/Controllers/UserController.php'
     * ]);
     */
    public function commentsWithFilters(
        string $owner,
        string $repo,
        int $number,
        array $filters = [],
    ): array {
        $response = $this->github()->connector()->send(new CommentsWithFilters(
            "{$owner}/{$repo}",
            $number,
            $filters
        ));

        return $response->dto();
    }

    /**
     * Batch fetch all comments from a pull request with author filtering.
     * Alias for commentsWithFilters() for backward compatibility.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $number  Pull request number
     * @param  array  $filters  Filtering options
     * @return array<PullRequestCommentDTO>
     */
    public function forPullRequest(
        string $owner,
        string $repo,
        int $number,
        array $filters = [],
    ): array {
        return $this->commentsWithFilters($owner, $repo, $number, $filters);
    }
}
