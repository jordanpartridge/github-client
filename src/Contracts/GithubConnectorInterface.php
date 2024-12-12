<?php

namespace JordanPartridge\GithubClient\Contracts;

use Saloon\Http\Request;
use Saloon\Http\Response;

interface GithubConnectorInterface
{
    /**
     * Set the authentication token.
     */
    public function setToken(string $token): self;

    /**
     * Get the current authentication token.
     */
    public function getToken(): ?string;

    /**
     * Send request, get response.
     */
    public function send(Request $request): Response;
}
