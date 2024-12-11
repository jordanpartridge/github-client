<?php

declare(strict_types=1);

namespace JordanPartridge\GithubClient\Enums;

enum MergeMethod: string
{
    case Merge = 'merge';
    case Squash = 'squash';
    case Rebase = 'rebase';
}
