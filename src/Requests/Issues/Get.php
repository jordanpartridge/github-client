<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Get extends Request
{
    protected Method $method = Method::GET;

    /**
     * Get a specific issue by number.
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param int $issue_number Issue number (must be positive)
     * @throws \InvalidArgumentException When issue number is invalid
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $issue_number,
    ) {
        if ($issue_number < 1) {
            throw new \InvalidArgumentException('Issue number must be a positive integer');
        }
    }

    /**
     * Convert the API response to an IssueDTO.
     *
     * @param Response $response The HTTP response from GitHub API
     * @return IssueDTO The issue data transfer object
     */
    public function createDtoFromResponse(Response $response): IssueDTO
    {
        return IssueDTO::fromApiResponse($response->json());
    }

    /**
     * Get the API endpoint for this request.
     *
     * @return string The GitHub API endpoint
     */
    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issue_number}";
    }
}
