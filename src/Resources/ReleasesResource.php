<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Releases\ReleaseData;
use JordanPartridge\GithubClient\Requests\Releases\Get;
use JordanPartridge\GithubClient\Requests\Releases\Index;
use JordanPartridge\GithubClient\Requests\Releases\Latest;

/**
 * GitHub Releases Resource Handler
 *
 * This class provides methods to interact with GitHub's releases endpoints.
 * It handles operations such as listing releases, getting specific releases,
 * and retrieving the latest release for a repository.
 *
 * @link https://docs.github.com/en/rest/releases/releases Documentation for GitHub Releases API
 *
 * Usage example:
 * ```php
 * $releases = $github->releases();
 *
 * // List all releases for a repository
 * $allReleases = $releases->all('owner', 'repo');
 *
 * // Get a specific release
 * $release = $releases->get('owner', 'repo', 123456);
 *
 * // Get the latest release
 * $latestRelease = $releases->latest('owner', 'repo');
 * ```
 */
readonly class ReleasesResource extends BaseResource
{
    /**
     * List releases for a repository
     *
     * Lists all releases for a repository. This includes draft releases visible
     * only to users with push access to the repository.
     *
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int|null  $per_page  Number of results per page (max 100)
     * @param  int|null  $page  Page number of the results to fetch
     * @return array<ReleaseData> Returns an array of release data objects
     *
     * @link https://docs.github.com/en/rest/releases/releases#list-releases
     *
     * Example Usage:
     * ```php
     * // Get all releases
     * $releases = $releases->all('owner', 'repo');
     *
     * // Get releases with pagination
     * $releases = $releases->all('owner', 'repo', per_page: 10, page: 1);
     * ```
     */
    public function all(
        string $owner,
        string $repo,
        ?int $per_page = null,
        ?int $page = null,
    ): array {
        return $this->github()->connector()->send(new Index(
            owner: $owner,
            repo: $repo,
            per_page: $per_page,
            page: $page,
        ))->dto();
    }

    /**
     * Get a specific release
     *
     * Gets a published release with the specified release ID.
     *
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int  $releaseId  The unique identifier of the release
     * @return ReleaseData Returns the release data
     *
     * @link https://docs.github.com/en/rest/releases/releases#get-a-release
     *
     * Example Usage:
     * ```php
     * $release = $releases->get('owner', 'repo', 123456);
     * echo $release->name; // Release name
     * echo $release->tag_name; // v1.0.0
     * ```
     */
    public function get(string $owner, string $repo, int $releaseId): ReleaseData
    {
        return $this->github()->connector()->send(new Get(
            owner: $owner,
            repo: $repo,
            releaseId: $releaseId,
        ))->dto();
    }

    /**
     * Get the latest release
     *
     * View the latest published full release for the repository.
     * The latest release is the most recent non-prerelease, non-draft release.
     *
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @return ReleaseData Returns the latest release data
     *
     * @link https://docs.github.com/en/rest/releases/releases#get-the-latest-release
     *
     * Example Usage:
     * ```php
     * $latestRelease = $releases->latest('owner', 'repo');
     * echo $latestRelease->tag_name; // Latest version tag
     * echo $latestRelease->published_at; // When it was published
     * ```
     */
    public function latest(string $owner, string $repo): ReleaseData
    {
        return $this->github()->connector()->send(new Latest(
            owner: $owner,
            repo: $repo,
        ))->dto();
    }
}
