<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestFileDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Files extends Request
{
    protected Method $method = Method::GET;

    private string $repo;

    private string $owner;

    private int $number;

    public function __construct(string $owner_repo, int $number)
    {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
        $this->number = $number;
    }

    public function createDtoFromResponse(Response $response): array
    {
        $data = $response->json();

        return array_map(
            function (array $file) {
                return PullRequestFileDTO::fromApiResponse($file);
            },
            $data,
        );
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls/%d/files', $this->owner, $this->repo, $this->number);
    }
}
