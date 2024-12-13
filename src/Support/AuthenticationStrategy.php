<?php

namespace JordanPartridge\GithubClient\Support;

interface AuthenticationStrategy
{
    /**
     * Get the authorization header for API requests.
     *
     * @return string
     */
    public function getAuthorizationHeader(): string;

    /**
     * Validate and refresh the authentication token if necessary.
     *
     * @return void
     */
    public function validateAndRefreshToken(): void;
}