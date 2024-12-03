<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Responses\PaginatedResponse;

class RepositoryResource extends Resource
{
    public function all(int $page = 1, int $perPage = 30): PaginatedResponse
    {
        return $this->paginated('user/repos', [
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    public function get(string $repository): array
    {
        return $this->connector->get("repos/{$repository}")->json();
    }
}
