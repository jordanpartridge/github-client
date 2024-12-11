<?php

declare(strict_types=1);

namespace JordanPartridge\GithubClient\Enums\Pulls;

enum Sort: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case POPULARITY = 'popularity';
    case LONG_RUNNING = 'long-running';
}
