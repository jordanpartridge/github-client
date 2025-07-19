<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Enums\MergeMethod;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class Merge extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    private string $repo;
    private string $owner;
    private int $number;

    public function __construct(
        string $owner_repo,
        int $number,
        protected ?string $commitMessage = null,
        protected ?string $sha = null,
        protected MergeMethod $mergeMethod = MergeMethod::Merge,
    ) {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
        $this->number = $number;
    }

    public function createDtoFromResponse(Response $response): array
    {
        return $response->json();
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls/%d/merge', $this->owner, $this->repo, $this->number);
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'commit_message' => $this->commitMessage,
            'sha' => $this->sha,
            'merge_method' => $this->mergeMethod->value,
        ]);
    }
}