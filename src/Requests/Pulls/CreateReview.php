<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestReviewDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateReview extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    private string $repo;

    private string $owner;

    private int $number;

    public function __construct(
        string $owner_repo,
        int $number,
        protected string $bodyText,
        protected string $event = 'COMMENT',
        protected array $comments = [],
    ) {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
        $this->number = $number;
    }

    public function createDtoFromResponse(Response $response): PullRequestReviewDTO
    {
        return PullRequestReviewDTO::fromApiResponse($response->json());
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls/%d/reviews', $this->owner, $this->repo, $this->number);
    }

    protected function defaultBody(): array
    {
        return [
            'body' => $this->bodyText,
            'event' => $this->event,
            'comments' => $this->comments,
        ];
    }
}
