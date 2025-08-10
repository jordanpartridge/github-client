<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueCommentDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetComment extends Request
{
    protected Method $method = Method::GET;

    /**
     * Get a single issue comment by ID.
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
     * Convert the API response to an IssueCommentDTO.
     *
     * @param  Response  $response  The HTTP response from GitHub API
     * @return IssueCommentDTO The issue comment data transfer object
     */
    public function createDtoFromResponse(Response $response): IssueCommentDTO
    {
        return IssueCommentDTO::fromApiResponse($response->json());
    }

    /**
     * Get the API endpoint for this request.
     *
     * @return string The GitHub API endpoint
     */
    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/comments/{$this->commentId}";
    }
}
