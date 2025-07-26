<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteComment extends Request
{
    protected Method $method = Method::DELETE;

    /**
     * Delete a pull request review comment.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $commentId  Comment ID (must be positive)
     *
     * @throws \InvalidArgumentException When comment ID is invalid
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $commentId,
    ) {
        if ($commentId < 1) {
            throw new \InvalidArgumentException('Comment ID must be a positive integer');
        }
    }

    /**
     * Get the API endpoint for this request.
     *
     * @return string The GitHub API endpoint
     */
    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/pulls/comments/{$this->commentId}";
    }
}
