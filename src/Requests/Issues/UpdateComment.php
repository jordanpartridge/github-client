<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueCommentDTO;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateComment extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    /**
     * Update an issue comment.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $commentId  Comment ID (must be positive)
     * @param  string  $bodyText  Updated comment body text
     *
     * @throws \InvalidArgumentException When comment ID is invalid
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $commentId,
        protected string $bodyText,
    ) {
        if ($commentId < 1) {
            throw new \InvalidArgumentException('Comment ID must be a positive integer');
        }
    }

    /**
     * Get the request body for updating a comment.
     *
     * @return array The request body
     *
     * @throws \InvalidArgumentException When comment body is empty
     */
    protected function defaultBody(): array
    {
        if (trim($this->bodyText) === '') {
            throw new \InvalidArgumentException('Comment body cannot be empty');
        }

        return [
            'body' => $this->bodyText,
        ];
    }

    /**
     * Convert the API response to an IssueCommentDTO.
     *
     * @param  Response  $response  The HTTP response from GitHub API
     * @return IssueCommentDTO The updated issue comment data transfer object
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
