<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class CommitAuthorData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        #[WithCast(DateTimeInterfaceCast::class)]
        public Carbon $date,
    ) {}
}
