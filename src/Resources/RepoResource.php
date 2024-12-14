<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Repos\Type;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Requests\Repos\Delete;
use JordanPartridge\GithubClient\Requests\Repos\Get;
use JordanPartridge\GithubClient\Requests\Repos\Index;
use JordanPartridge\GithubClient\Requests\Repos\Issues;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Response;

/**
 * GitHub Repository Resource Handler
 *
 * This class provides methods to interact with GitHub's repository endpoints.
 */
readonly class RepoResource extends BaseResource
{
    /**
     * List repositories for the authenticated user
     */
    public function all(
        ?int $per_page = null,
        ?int $page = null,
        ?Visibility $visibility = null,
        ?Sort $sort = null,
        ?Direction $direction = null,
        ?Type $type = null,
    ): Response {
        return $this->connector()->send(new Index(
            per_page: $per_page,
            page: $page,
            visibility: $visibility,
            sort: $sort,
            direction: $direction,
            type: $type,
        ));
    }

    /**
     * Get a specific repository by full name
     */
    public function get(Repo $repo): RepoData
    {
        return $this->connector()->send(new Get($repo))->dto();
    }

    /**
     * Delete a repository
     */
    public function delete(string $full_name): Response
    {
        return $this->connector()->send(new Delete($full_name));
    }

    /**
     * List repository issues
     *
     * @param  string|Repo  $repo  Repository identifier
     * @param  int|null  $per_page  Number of results per page (max 100)
     * @param  int|null  $page  Page number
     * @param  string|null  $state  Filter by state (open, closed, all)
     */
    public function issues(string|Repo $repo, ?int $per_page = null, ?int $page = null, ?string $state = null): Response
    {
        if (is_string($repo)) {
            $repo = Repo::from($repo);
        }

        return $this->connector()->send(new Issues(
            repo: $repo,
            per_page: $per_page,
            page: $page,
            state: $state
        ));
    }
}
