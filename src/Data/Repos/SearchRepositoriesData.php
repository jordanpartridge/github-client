<?php

namespace JordanPartridge\GithubClient\Data\Repos;

use Spatie\LaravelData\Data;

class SearchRepositoriesData extends Data
{
    /**
     * @param  int  $total_count  Total number of repositories found
     * @param  bool  $incomplete_results  Whether the results are incomplete
     * @param  RepoData[]  $items  Array of repository data objects
     */
    public function __construct(
        public int $total_count,
        public bool $incomplete_results,
        public array $items,
    ) {}
}