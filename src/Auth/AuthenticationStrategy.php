<?php

namespace JordanPartridge\GithubClient\Auth;

/**
 * Interface for different GitHub authentication strategies.
 */
interface AuthenticationStrategy
{
    /**
     * Get the authorization header value for API requests.
     */
    public function getAuthorizationHeader(): string;

    /**
     * Validate that the authentication credentials are properly configured.
     *
     * @throws \JordanPartridge\GithubClient\Exceptions\AuthenticationException
     */
    public function validate(): void;

    /**
     * Get the authentication type identifier.
     */
    public function getType(): string;

    /**
     * Check if the authentication needs to be refreshed.
     */
    public function needsRefresh(): bool;

    /**
     * Refresh the authentication if needed.
     *
     * @throws \JordanPartridge\GithubClient\Exceptions\AuthenticationException
     */
    public function refresh(): void;
}