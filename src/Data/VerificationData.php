<?php

namespace JordanPartridge\GithubClient\Data;

use Spatie\LaravelData\Data;

class VerificationData extends Data
{
    public function __construct(
        public bool $verified,
        public string $reason,
        public ?string $signature,
        public ?string $payload,
        public ?string $verified_at,
    ) {}
}
