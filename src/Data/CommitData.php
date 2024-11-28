<?php

namespace JordanPartridge\GithubClient\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CommitData extends Data
{
    public function __construct(
        public string $sha,
        public string $node_id,
        public CommitDetailsData $commit,
        public string $url,
        public string $html_url,
        public string $comments_url,
        public GitUserData $author,
        public GitUserData $committer,
        public array $parents,
    ) {}
}
