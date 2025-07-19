<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class Create extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    private string $repo;

    private string $owner;

    public function __construct(
        string $owner_repo,
        protected string $title,
        protected string $head,
        protected string $base,
        protected string $bodyText = '',
        protected bool $draft = false,
    ) {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
    }

    public function createDtoFromResponse(Response $response): PullRequestDTO
    {
        return PullRequestDTO::fromApiResponse($response->json());
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls', $this->owner, $this->repo);
    }

    protected function defaultBody(): array
    {
        return [
            'title' => $this->title,
            'head' => $this->head,
            'base' => $this->base,
            'body' => $this->bodyText,
            'draft' => $this->draft,
        ];
    }
}
