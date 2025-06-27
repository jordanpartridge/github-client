<?php

namespace JordanPartridge\GithubClient\Requests\Commits;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;
use JordanPartridge\GithubClient\Data\Commits\CommitData;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use JsonException;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Get extends Request
{
    use ValidatesRepoName;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly Repo $repo,
        private readonly string $commit_sha,
    ) {
        $this->validateSHA($commit_sha);
    }

    public function resolveEndpoint(): string
    {
        return '/repos/'.$this->repo->fullName().'/commits/'.$this->commit_sha;
    }

    private function validateSHA(string $commit_sha): void
    {
        if (! preg_match('/^[0-9a-f]{40}$/i', $commit_sha)) {
            throw new InvalidArgumentException('Invalid commit SHA format');
        }
    }

    /**
     * @throws JsonException
     */
    public function createDtoFromResponse(Response $response): CommitData
    {
        return CommitData::fromArray($response->json());
    }
}
