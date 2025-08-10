<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\Params;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTOFactory;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestSummaryDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Enhanced Index request that explicitly returns PullRequestSummaryDTO objects.
 *
 * This request is designed for the GitHub API list endpoint and clearly
 * communicates that it returns summary data without detailed statistics.
 * Use this when you need fast PR listings without comment counts.
 */
class IndexWithSummaryDTO extends Request
{
    protected Method $method = Method::GET;

    private string $repo;

    private string $owner;

    private Params $parameters;

    /**
     * @param  string  $owner_repo  - eg jordanpartridge/github-client
     * @param  array  $parameters  Optional query parameters
     */
    public function __construct(string $owner_repo, array $parameters = [])
    {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
        $this->parameters = Params::fromArray($parameters);
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls', $this->owner, $this->repo);
    }

    /**
     * Create Summary DTOs from response.
     *
     * @return array<PullRequestSummaryDTO>
     */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            fn (array $pullRequest) => PullRequestDTOFactory::createSummary($pullRequest),
            $response->json(),
        );
    }

    public function defaultQuery(): array
    {
        return [
            'state' => $this->parameters->state,
            'head' => $this->parameters->head,
            'base' => $this->parameters->base,
            'sort' => $this->parameters->sort?->value,
            'direction' => $this->parameters->direction?->value,
            'per_page' => $this->parameters->per_page,
            'page' => $this->parameters->page,
        ];
    }
}
