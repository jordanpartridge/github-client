<?php

declare(strict_types=1);

namespace JordanPartridge\GithubClient\Enums;
enum Visibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case INTERNAL = 'internal';
}
