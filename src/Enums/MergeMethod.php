<?php

declare(strict_types=1);

namespace ShadeTheory\GithubClient\Enums;

enum MergeMethod: string
{
    case Merge = 'merge';
    case Squash = 'squash';
    case Rebase = 'rebase';
}
