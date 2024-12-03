<?php

namespace JordanPartridge\GithubClient\Enums;

enum MergeMethod: string
{
    case Merge = 'merge';
    case Squash = 'squash';
    case Rebase = 'rebase';
}
