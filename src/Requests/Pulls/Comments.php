<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Comments extends Request
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
        return array_map(
            fn (array $comment) => PullRequestCommentDTO::fromApiResponse($comment),
            $response->json()
        );
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls/%d/comments', $this->owner, $this->repo, $this->number);
    }
}