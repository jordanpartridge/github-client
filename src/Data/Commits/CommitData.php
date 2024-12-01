<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use JordanPartridge\GithubClient\Data\GitUserData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CommitData extends Data
{
    public function __construct(
        public string $sha,
        public string $node_id,
        public CommitDetailsData $commit,
        public string $url,
        public string $html_url,
        public string $comments_url,
        public ?GitUserData $author,
        public ?GitUserData $committer,
        public array $parents,
        public ?CommitStatsData $stats = null,
        #[DataCollectionOf(CommitFileData::class)]
        public ?DataCollection $files = null,
    ) {}
}
