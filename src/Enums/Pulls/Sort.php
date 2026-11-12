<?php

declare(strict_types=1);

namespace ShadeTheory\GithubClient\Enums\Pulls;

enum Sort: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case POPULARITY = 'popularity';
    case LONG_RUNNING = 'long-running';
}
