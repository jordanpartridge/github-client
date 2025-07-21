<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDetailDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTOFactory;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Enhanced Get request that explicitly returns PullRequestDetailDTO objects.
 *
 * This request is designed for the GitHub API individual endpoint and clearly
 * communicates that it returns detailed data including comment counts and
 * code statistics. Use this when you need complete PR information.
 */
class GetWithDetailDTO extends Request
{
    protected Method $method = Method::GET;

    private string $repo;

    private string $owner;

    private int $number;

    /**
     * @param  string  $owner_repo  - eg jordanpartridge/github-client
     * @param  int  $number  Pull request number
     */
    public function __construct(string $owner_repo, int $number)
    {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
        $this->number = $number;
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls/%d', $this->owner, $this->repo, $this->number);
    }

    /**
     * Create Detail DTO from response.
     */
    public function createDtoFromResponse(Response $response): PullRequestDetailDTO
    {
        return PullRequestDTOFactory::createDetail($response->json());
    }
}
