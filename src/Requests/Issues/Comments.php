<?php

namespace JordanPartridge\GithubClient\Requests\Issues;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Data\Issues\IssueCommentDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Comments extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $issue_number,
        protected ?int $per_page = null,
        protected ?int $page = null,
        protected ?string $since = null,
    ) {
        if ($issue_number < 1) {
            throw new InvalidArgumentException('Issue number must be a positive integer');
        }
        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
            throw new InvalidArgumentException('Per page must be between 1 and 100');
        }
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'per_page' => $this->per_page,
            'page' => $this->page,
            'since' => $this->since,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            fn (array $comment) => IssueCommentDTO::fromApiResponse($comment),
            $response->json(),
        );
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->issue_number}/comments";
    }
}
