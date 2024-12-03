<?php

namespace JordanPartridge\GithubClient\Concerns;

use Closure;
use Generator;
use JordanPartridge\GithubClient\Responses\PaginatedResponse;

trait HandlesPagination
{
    public function paginate(Closure $resource): Generator
    {
        $page = 1;

        do {
            /** @var PaginatedResponse $response */
            $response = $resource($page);
            
            foreach ($response->json() as $item) {
                yield $item;
            }

            $page++;
        } while ($response->hasNextPage());
    }
}
