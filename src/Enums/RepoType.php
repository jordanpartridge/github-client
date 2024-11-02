<?php

namespace JordanPartridge\GithubClient\Enums;

enum RepoType: string
{
    case All = 'all';
    case Public = 'public';
    case Private = 'private';
    case Forks = 'forks';
    case Sources = 'sources';
    case Member = 'member';
}
