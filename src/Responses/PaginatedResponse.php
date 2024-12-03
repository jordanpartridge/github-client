<?php

namespace JordanPartridge\GithubClient\Responses;

use Saloon\Http\Response;

class PaginatedResponse extends Response
{
    public function hasNextPage(): bool
    {
        return $this->header('Link')?->contains('rel="next"') ?? false;
    }

    public function hasPreviousPage(): bool
    {
        return $this->header('Link')?->contains('rel="prev"') ?? false;
    }

    public function getNextPageUrl(): ?string
    {
        if (! $this->hasNextPage()) {
            return null;
        }

        preg_match('/<([^>]*)>;\s*rel="next"/', $this->header('Link'), $matches);

        return $matches[1] ?? null;
    }

    public function getPreviousPageUrl(): ?string
    {
        if (! $this->hasPreviousPage()) {
            return null;
        }

        preg_match('/<([^>]*)>;\s*rel="prev"/', $this->header('Link'), $matches);

        return $matches[1] ?? null;
    }

    public function getTotalCount(): ?int
    {
        return (int) $this->header('X-Total-Count');
    }

    public function getCurrentPage(): int
    {
        $url = $this->request?->getUrl();
        if (! $url) {
            return 1;
        }

        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return (int) ($query['page'] ?? 1);
    }
}
