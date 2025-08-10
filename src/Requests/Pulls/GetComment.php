<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetComment extends Request
{
    protected Method $method = Method::GET;

    /**
     * Get a single pull request review comment by ID.
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
     * Convert the API response to a PullRequestCommentDTO.
     *
     * @param  Response  $response  The HTTP response from GitHub API
     *
     * @return PullRequestCommentDTO The pull request comment data transfer object
     */
    public function createDtoFromResponse(Response $response): PullRequestCommentDTO
    {
        return PullRequestCommentDTO::fromApiResponse($response->json());
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
