<?php

declare(strict_types=1);

namespace JordanPartridge\GithubClient\Enums;

enum Sort: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case PUSHED = 'pushed';
    case FULL_NAME = 'full_name';
}
