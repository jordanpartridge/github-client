<?php

namespace JordanPartridge\GithubClient\Support;

interface AuthenticationStrategy
{
    /**
     * Get the authorization header for API requests.
     */
    public function getAuthorizationHeader(): string;

    /**
     * Validate and refresh the authentication token if necessary.
     */
    public function validateAndRefreshToken(): void;
}
