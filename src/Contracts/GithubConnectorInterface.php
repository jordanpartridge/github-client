<?php

namespace JordanPartridge\GithubClient\Contracts;

use Saloon\Http\Request;
use Saloon\Http\Response;

interface GithubConnectorInterface
{
    public function __construct(string $token);

    public function resolveBaseUrl(): string;

    public function send(Request $request): Response;
}
