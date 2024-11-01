<?php

namespace JordanPartridge\GithubClient\Contracts;

use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Request;
use Saloon\Http\Response;

interface GithubConnectorInterface
{
    public function __construct(string $token);

    public function resolveBaseUrl(): string;

    public function defaultOauthConfig(): OAuthConfig;

    public function send(Request $request): Response;
}
