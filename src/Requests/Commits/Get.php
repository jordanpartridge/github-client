<?php

namespace JordanPartridge\GithubClient\Requests\Commits;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;
use JordanPartridge\GithubClient\Data\CommitDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Get extends Request
{
    use ValidatesRepoName;

    /**
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * @param Repo $repo
     * @param string $commit_sha
     */
    public function __construct(
        private readonly Repo $repo,
        private readonly string $commit_sha,
    )
    {
        $this->validateSHA($commit_sha);
    }

    public function resolveEndpoint(): string
    {
        return '/repos/' . $this->repo->fullName() . '/commits/' . $this->commit_sha;
    }

    private function validateSHA(string $commit_sha): void
    {
        if (! preg_match('/^[0-9a-f]{40}$/i', $commit_sha)) {
            throw new InvalidArgumentException('Invalid commit SHA format');
        }
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return CommitDTO::fromArray(data: $response->json());
    }

}
