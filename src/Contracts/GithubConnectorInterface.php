<?php

namespace JordanPartridge\GithubClient\Contracts;

use Saloon\Http\Request;
use Saloon\Http\Response;

interface GithubConnectorInterface
{
    /**
     * Set the authentication token.
     *
     * @param string $token
     * @return self
     */
    public function setToken(string $token): self;

    /**
     * Get the current authentication token.
     *
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * Send request, get response.
     *
     * @param Request $request
     * @return Response
     */
    public function send(Request $request): Response;
}
