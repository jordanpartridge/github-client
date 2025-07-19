<?php

namespace JordanPartridge\GithubClient\Enums\Issues;

enum Sort: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case COMMENTS = 'comments';
}
