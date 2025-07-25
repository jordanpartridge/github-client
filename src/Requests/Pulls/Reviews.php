<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use Illuminate\Support\Collection;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestReviewDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Reviews extends Request
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

    public function createDtoFromResponse(Response $response): Collection
    {
        $reviews = array_map(
            function (array $review) {
                return PullRequestReviewDTO::fromApiResponse($review);
            },
            $response->json()
        );

        return collect($reviews);
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls/%d/reviews', $this->owner, $this->repo, $this->number);
    }
}
