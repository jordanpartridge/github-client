<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueCommentDTO;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateComment extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * Create a comment on an issue.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  int  $issue_number  Issue number (must be positive)
     * @param  string  $bodyText  Comment body text
     *
     * @throws \InvalidArgumentException When issue number is invalid
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $issue_number,
        protected string $bodyText,
    ) {
        if ($issue_number < 1) {
            throw new \InvalidArgumentException('Issue number must be a positive integer');
        }
    }

    /**
     * Get the request body for creating a comment.
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
     *
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
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issue_number}/comments";
    }
}
