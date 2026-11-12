<?php

namespace ShadeTheory\GithubClient\Enums\Issues;

enum Sort: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case COMMENTS = 'comments';
}
