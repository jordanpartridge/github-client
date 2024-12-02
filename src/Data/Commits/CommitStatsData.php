<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use Spatie\LaravelData\Data;

class CommitStatsData extends Data
{
    public function __construct(
        public int $total,
        public int $additions,
        public int $deletions,
    ) {}
}
