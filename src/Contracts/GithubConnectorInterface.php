<?php

namespace JordanPartridge\GithubClient\Contracts;

use JordanPartridge\GithubClient\Resources\RepoResource;
use Saloon\Http\Request;
use Saloon\Http\Response;

interface GithubConnectorInterface
{
    public function __construct(string $token);

    public function resolveBaseUrl(): string;

    public function send(Request $request): Response;

    public function repos(): RepoResource;
}
