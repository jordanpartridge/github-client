<?php

namespace JordanPartridge\GithubClient\Requests\Commits;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Data\Commits\CommitData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Index extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $repo_name,
        protected ?int $per_page = null,
        protected ?int $page = null,
    ) {
        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
            throw new InvalidArgumentException('Per page must be between 1 and 100');
        }
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'per_page' => $this->per_page,
            'page' => $this->page,
        ], fn ($value) => $value !== null);
    }

    public function resolveEndpoint(): string
    {
        return '/repos/' . $this->repo_name . '/commits';
    }

    public function createDtoFromResponse(Response $response): array
    {
        if ($response->status() == 409) {
            /**
             * A 409 Conflict status is returned when:
             * 1. The repository is empty (no commits)
             * 2. The repository has just been created
             * 3. The default branch doesn't exist yet
             *
             * Since these scenarios indicate no commits are available, returning an empty array
             * is the most sensible default behavior. This matches the expected data structure
             * while accurately representing the repository's state.
             *
             * Note: Future updates might include a configuration option to handle this differently
             * if specific use cases require alternative behavior.
             */
            return [];
        }

        return $response->collect()->map(function (array $commit) {
            return CommitData::fromArray($commit);
        })->all();
    }
}
