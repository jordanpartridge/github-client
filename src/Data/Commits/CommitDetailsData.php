<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use JordanPartridge\GithubClient\Data\TreeData;
use JordanPartridge\GithubClient\Data\VerificationData;
use Spatie\LaravelData\Data;

class CommitDetailsData extends Data
{
    public function __construct(
        public CommitAuthorData $author,
        public CommitAuthorData $committer,
        public string $message,
        public TreeData $tree,
        public string $url,
        public int $comment_count,
        public VerificationData $verification,
    ) {}
}
