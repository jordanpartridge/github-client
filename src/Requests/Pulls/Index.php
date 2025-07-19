<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\Params;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Index extends Request
{
    /**
     * The http method for this request.
     */
    protected Method $method = Method::GET;

    /**
     * The repo extracted through the value object.
     */
    private string $repo;

    /**
     * The owner extracted through the value object.
     */
    private string $owner;

    /**
     * The optional parameters for filtering the request results.
     */
    private Params $parameters;

    /**
     * Validate the owner_repo string and set the owner and repo properties.
     *
     * @see https://docs.github.com/en/rest/pulls/pulls?apiVersion=2022-11-28
     *
     * @param  string  $owner_repo  - eg jordanpartridge/github-client
     */
    public function __construct(string $owner_repo, array $parameters = [])
    {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
        $this->parameters = Params::fromArray($parameters);
    }

    /**
     * Resolves the endpoint URL for retrieving all pull requests.
     * This should be in the format of: "/repos/{owner}/{repo}/pulls"
     */
    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls', $this->owner, $this->repo);
    }

    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            fn (array $pullRequest) => PullRequestDTO::fromApiResponse($pullRequest),
            $response->json()
        );
    }

    /**
     * @return array|array
     */
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
