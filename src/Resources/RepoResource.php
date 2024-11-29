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
use Saloon\Http\Response;

/**
 * GitHub Repository Resource Handler
 *
 * This class provides methods to interact with GitHub's repository endpoints.
 * It handles operations such as listing, creating, updating, and deleting repositories,
 * as well as managing repository settings and metadata.
 *
 * @link https://docs.github.com/en/rest/repos/repos Documentation for GitHub Repository API
 *
 * Usage example:
 * ```php
 * $repos = new RepoResource($connector);
 *
 * // List all public repositories
 * $response = $repos->all(
 *     per_page: 30,
 *     visibility: Visibility::PUBLIC,
 *     sort: Sort::CREATED,
 *     direction: Direction::DESC
 * );
 * ```
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
     * @param  int|null  $per_page  Number of results per page (max 100)
     * @param  int|null  $page  Page number of the results to fetch
     * @param  Visibility|null  $visibility  Filter repositories by visibility (public, private, all)
     * @param  Sort|null  $sort  Sort repositories by field (created, updated, pushed, full_name)
     * @param  Direction|null  $direction  Sort direction (asc or desc)
     * @return Response Returns a Saloon response containing the repository data
     *
     * @link https://docs.github.com/en/rest/repos/repos#list-repositories-for-the-authenticated-user
     *
     * Example Response:
     * ```json
     * [
     *   {
     *     "id": 1296269,
     *     "node_id": "MDEwOlJlcG9zaXRvcnkxMjk2MjY5",
     *     "name": "Hello-World",
     *     "full_name": "octocat/Hello-World",
     *     "owner": {
     *       "login": "octocat",
     *       "id": 1
     *     },
     *     "private": false,
     *     "description": "This your first repo!",
     *     "visibility": "public"
     *   }
     * ]
     * ```
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
     * Get a specific repository by full name
     *
     * Retrieves detailed information about a specific repository. The repository name
     * should be in the format "owner/repo" (e.g., "octocat/Hello-World").
     *
     * @param  string  $full_name  The full name of the repository (owner/repo)
     * @return Response Returns a Saloon response containing the repository details
     *
     * @link https://docs.github.com/en/rest/repos/repos#get-a-repository
     *
     * Example Usage:
     * ```php
     * $repo = $repos->get('jordanpartridge/github-client');
     * $details = $repo->json();
     * ```
     */
    public function get(\JordanPartridge\GithubClient\ValueObjects\Repo $repo): RepoData
    {
        return $this->connector()->send(new Get($repo))->dto();
    }

    public function delete(string $full_name): Response
    {
        return $this->connector()->send(new Delete($full_name));
    }
}
