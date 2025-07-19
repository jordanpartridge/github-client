<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class Update extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    private string $repo;
    private string $owner;
    private int $number;

    public function __construct(
        string $owner_repo,
        int $number,
        protected array $parameters = [],
    ) {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
        $this->number = $number;
    }

    public function createDtoFromResponse(Response $response): PullRequestDTO
    {
        return PullRequestDTO::fromApiResponse($response->json());
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls/%d', $this->owner, $this->repo, $this->number);
    }

    protected function defaultBody(): array
    {
        return $this->parameters;
    }
}