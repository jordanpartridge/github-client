<?php

namespace JordanPartridge\GithubClient\Requests\Files;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Request to fetch files for a specific commit in a GitHub repository
 *
 * @see https://docs.github.com/en/rest/commits/commits#list-files-in-a-commit
 * + */
class Index extends Request
{
    use ValidatesRepoName;

    /**
     * @var Method The HTTP method for this request
     */
    protected Method $method = Method::GET;

    /**
     * @param $repo_name - full repo name
     * @param $commit_sha - sha for commit
     */
    public function __construct(private $repo_name, private $commit_sha)
    {
        Repo::fromFullName($this->repo_name);
        if (!preg_match('/^[0-9a-f]{40}$/i', $commit_sha)) {
            throw new InvalidArgumentException('Invalid commit SHA format');
        }
    }

    public function resolveEndpoint(): string
    {
        return 'repos/' . $this->repo_name . '/commits/' . $this->commit_sha . '/files';
    }
}
