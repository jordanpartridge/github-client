<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\GithubConnector;
use JordanPartridge\GithubClient\Responses\PaginatedResponse;

abstract class Resource
{
    public function __construct(
        protected readonly GithubConnector $connector
    ) {}

    protected function paginated(string $endpoint, array $query = []): PaginatedResponse
    {
        return $this->connector
            ->get($endpoint, $query)
            ->withResponseType(PaginatedResponse::class);
    }
}
