<?php

declare(strict_types=1);

namespace JordanPartridge\GithubClient\Enums\Repos;

enum Type: string
{
    case All = 'all';
    case Public = 'public';
    case Private = 'private';
    case Forks = 'forks';
    case Sources = 'sources';
    case Member = 'member';
    case Owner = 'owner';
}
