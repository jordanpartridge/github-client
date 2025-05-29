<?php

namespace JordanPartridge\GithubClient\Contracts;

use JordanPartridge\GithubClient\Resources\ActionsResource;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
use JordanPartridge\GithubClient\Resources\RepoResource;
use Saloon\Http\Request;
use Saloon\Http\Response;

interface GithubConnectorInterface
{
    // Resource getters
    public function repos(): RepoResource;

    public function commits(): CommitResource;

    public function files(): FileResource;

    public function pullRequests(): PullRequestResource;

    public function actions(): ActionsResource;

    // HTTP methods
    /**
     * Send a raw Saloon request to the GitHub API
     *
     * @param  Request  $request  The Saloon request to send
     * @return Response The Saloon response
     */
    public function send(Request $request): Response;

    /**
     * Make a GET request to the GitHub API
     *
     * @param  string  $url  The endpoint URL
     * @param  array  $parameters  Query parameters
     * @return array Response data as array
     */
    public function get(string $url, array $parameters = []): array;

    /**
     * Make a POST request to the GitHub API
     *
     * @param  string  $url  The endpoint URL
     * @param  array  $parameters  Request body data
     * @return array Response data as array
     */
    public function post(string $url, array $parameters = []): array;

    /**
     * Make a PATCH request to the GitHub API
     *
     * @param  string  $url  The endpoint URL
     * @param  array  $parameters  Request body data
     * @return array Response data as array
     */
    public function patch(string $url, array $parameters = []): array;

    /**
     * Make a PUT request to the GitHub API
     *
     * @param  string  $url  The endpoint URL
     * @param  array  $parameters  Request body data
     * @return array Response data as array
     */
    public function put(string $url, array $parameters = []): array;

    /**
     * Make a DELETE request to the GitHub API
     *
     * @param  string  $url  The endpoint URL
     * @param  array  $parameters  Query parameters
     * @return array Response data as array
     */
    public function delete(string $url, array $parameters = []): array;
}
