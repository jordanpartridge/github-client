<?php

namespace JordanPartridge\GithubClient\Data\Repos;

use Spatie\LaravelData\Data;

class LicenseData extends Data
{
    public function __construct(
        public string $key,
        public string $name,
        public string $spdx_id,
        public ?string $url,
        public string $node_id,
    ) {}
}
