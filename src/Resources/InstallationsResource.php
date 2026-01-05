<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Installations\InstallationData;
use JordanPartridge\GithubClient\Data\Installations\InstallationTokenData;
use JordanPartridge\GithubClient\Requests\Installations\CreateAccessToken;
use JordanPartridge\GithubClient\Requests\Installations\GetInstallation;
use JordanPartridge\GithubClient\Requests\Installations\ListInstallations;

/**
 * GitHub App Installations Resource Handler
 *
 * This class provides methods to interact with GitHub App installation endpoints.
 * It handles operations such as listing installations, getting installation details,
 * and creating installation access tokens.
 *
 * @link https://docs.github.com/en/rest/apps/installations
 */
readonly class InstallationsResource extends BaseResource
{
    /**
     * List all installations for the authenticated GitHub App.
     *
     * Requires GitHub App authentication (JWT token).
     *
     * @param  int|null  $per_page  Number of results per page (max 100)
     * @param  int|null  $page  Page number of the results to fetch
     *
     * @return array<InstallationData> Array of installation data objects
     *
     * @link https://docs.github.com/en/rest/apps/installations#list-installations-for-the-authenticated-app
     */
    public function list(?int $per_page = null, ?int $page = null): array
    {
        $response = $this->connector()->send(new ListInstallations($per_page, $page));

        return $response->dto();
    }

    /**
     * Get details about a specific installation.
     *
     * Requires GitHub App authentication (JWT token).
     *
     * @param  int  $installationId  The installation ID
     *
     * @return InstallationData The installation data
     *
     * @link https://docs.github.com/en/rest/apps/installations#get-an-installation-for-the-authenticated-app
     */
    public function get(int $installationId): InstallationData
    {
        $response = $this->connector()->send(new GetInstallation($installationId));

        return $response->dto();
    }

    /**
     * Create an installation access token.
     *
     * Generates a new access token that can be used to make authenticated
     * requests on behalf of the installation. Tokens expire after 1 hour.
     *
     * Requires GitHub App authentication (JWT token).
     *
     * @param  int  $installationId  The installation ID
     * @param  array|null  $repositories  Optional array of repository names to limit access
     * @param  array|null  $permissions  Optional permissions to request
     *
     * @return InstallationTokenData The installation access token data
     *
     * @link https://docs.github.com/en/rest/apps/installations#create-an-installation-access-token-for-an-app
     */
    public function createAccessToken(
        int $installationId,
        ?array $repositories = null,
        ?array $permissions = null,
    ): InstallationTokenData {
        $response = $this->connector()->send(
            new CreateAccessToken($installationId, $repositories, $permissions),
        );

        return $response->dto();
    }

    /**
     * List all installations with automatic pagination.
     *
     * This method automatically fetches all installations across multiple pages.
     *
     * @param  int|null  $per_page  Number of results per page (max 100, default 100)
     *
     * @return array<InstallationData> Array of all installation data objects
     */
    public function listAll(?int $per_page = 100): array
    {
        $page = 1;
        $allInstallations = [];
        $maxPages = 100;

        do {
            if ($page > $maxPages) {
                throw new \RuntimeException("Maximum page limit ($maxPages) exceeded during pagination");
            }

            $response = $this->connector()->send(new ListInstallations($per_page, $page));
            $installations = $response->dto();

            if (! empty($installations)) {
                $allInstallations = array_merge($allInstallations, $installations);
            }

            $linkHeader = $response->header('Link');
            $hasNextPage = $linkHeader && str_contains($linkHeader, 'rel="next"');

            $page++;
        } while ($hasNextPage && ! empty($installations));

        return $allInstallations;
    }
}
