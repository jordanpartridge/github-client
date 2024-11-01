<?php

namespace JordanPartridge\GithubClient\Contracts;

use Saloon\Helpers\OAuth2\OAuthConfig;

interface GithubConnectorInterface
{
    /**
     * @param string $token
     */
    public function __construct(string $token);

    public function resolveBaseUrl(): string;

    public function defaultOauthConfig(): OAuthConfig;

    public function resolveEndpoint(): string;

    public function resolveMethod(): string;


}
