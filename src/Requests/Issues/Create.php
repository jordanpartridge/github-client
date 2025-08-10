<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class Create extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * Create a new issue in a repository.
     *
     * @param  string  $owner  Repository owner
     * @param  string  $repo  Repository name
     * @param  string  $title  Issue title (required)
     * @param  string|null  $bodyText  Issue body content
     * @param  array|null  $assignees  Array of logins for users to assign to this issue
     * @param  int|null  $milestone  Milestone to associate with this issue
     * @param  array|null  $labels  Array of label names to associate with this issue
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected string $title,
        protected ?string $bodyText = null,
        protected ?array $assignees = null,
        protected ?int $milestone = null,
        protected ?array $labels = null,
    ) {}

    /**
     * Get the request body for creating an issue.
     *
     * @return array The request body with filtered null values
     */
    protected function defaultBody(): array
    {
        return array_filter([
            'title' => $this->title,
            'body' => $this->bodyText,
            'assignees' => $this->assignees,
            'milestone' => $this->milestone,
            'labels' => $this->labels,
        ], fn ($value) => $value !== null);
    }

    /**
     * Convert the API response to an IssueDTO.
     *
     * @param  Response  $response  The HTTP response from GitHub API
     *
     * @return IssueDTO The created issue data transfer object
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
        return "/repos/{$this->owner}/{$this->repo}/issues";
    }
}
