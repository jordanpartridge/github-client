<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CommitAuthorData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public Carbon $date,
    )
    {
    }
}
