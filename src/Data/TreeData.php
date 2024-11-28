<?php

namespace JordanPartridge\GithubClient\Data;

use Spatie\LaravelData\Data;
class TreeData extends Data
{
    public function __construct(
    public string $sha,
    public string $url,
) {}
}
