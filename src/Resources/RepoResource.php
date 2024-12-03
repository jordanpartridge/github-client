<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\RepoType;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Requests\Repos\Delete;
use JordanPartridge\GithubClient\Requests\Repos\Get;
use JordanPartridge\GithubClient\Requests\Repos\Index;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Response;

/**
 * GitHub Repository Resource Handler
 *
 * This class provides methods to interact with GitHub's repository endpoints.
 * It handles operations such as listing, creating, updating, and deleting repositories,
 * as well as managing repository settings and metadata.
 *
 * @link https://docs.github.com/en/rest/repos/repos Documentation for GitHub Repository API
 */
readonly class RepoResource extends BaseResource
{
    /**
     * List repositories for the authenticated user
     *
     * Retrieves repositories that the authenticated user has explicit permission
     * to access. This includes owned repositories, collaborated repositories,
     * and organization repositories where the user has appropriate access.
     *
     * @param int|null $per_page Number of results per page (max 100)
     * @param int|null $page Page number of the results to fetch
     * @param Visibility|null $visibility Filter repositories by visibility (public, private, all)
     * @param Sort|null $sort Sort repositories by field (created, updated, pushed, full_name)
     * @param Direction|null $direction Sort direction (asc or desc)
     * @param RepoType|null $type Type of repositories to return
     * @return Response Returns a Saloon response containing the repository data
     *
     * @link https://docs.github.com/en/rest/repos/repos#list-repositories-for-the-authenticated-user
     */
    public function all(
        ?int $per_page = null,
        ?int $page = null,
        ?Visibility $visibility = null,
        ?Sort $sort = null,
        ?Direction $direction = null,
        ?RepoType $type = null,
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
     * Get a specific repository
     *
     * @param Repo $repo Repository to retrieve
     * @return RepoData Returns repository details as a data object
     *
     * @link https://docs.github.com/en/rest/repos/repos#get-a-repository
     *
     * Example Usage:
     * ```php
     * $repo = Repo::fromFullName('owner/repo');
     * $details = $repos->get($repo);
     * ```
     */
    public function get(Repo $repo): RepoData
    {
        return $this->connector()->send(new Get($repo))->dto();
    }

    /**
     * Delete a repository
     *
     * @param Repo $repo Repository to delete
     * @return Response Response indicating success/failure
     *
     * @link https://docs.github.com/en/rest/repos/repos#delete-a-repository
     *
     * Example Usage:
     * ```php
     * $repo = Repo::fromFullName('owner/repo');
     * $response = $repos->delete($repo);
     * ```
     */
    public function delete(Repo $repo): Response
    {
        return $this->connector()->send(new Delete($repo->getFullName()));
    }
}
