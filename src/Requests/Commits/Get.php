<?php

namespace JordanPartridge\GithubClient\Requests\Commits;

use InvalidArgumentException;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class Get extends Request
{
    /**
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * @param string $commit_sha
     */
    public function __construct(
        private readonly string $commit_sha,
    )
    {
       $this->validateSHA($commit_sha);
    }

    public function resolveEndpoint(): string
    {
        return '/commits/' . $this->commit_sha;
    }

    private function validateSHA(string $commit_sha): void
    {
        if (!preg_match('/^[0-9a-f]{40}$/i', $commit_sha)) {
            throw new InvalidArgumentException('Invalid commit SHA format');
        }
    }


}
