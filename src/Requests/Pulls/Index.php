<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * @see https://docs.github.com/en/rest/pulls/pulls?apiVersion=2022-11-28
 */
class Index extends Request
{
    protected Method $method = Method::GET;
    private string $repo;
    private string $owner;

    public function __construct(string $owner_repo)
    {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
    }

    /**
     * {@inheritDoc}
     */
    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls', $this->owner, $this->repo);
    }
}
