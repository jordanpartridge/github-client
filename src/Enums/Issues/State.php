<?php

namespace JordanPartridge\GithubClient\Enums\Issues;

enum State: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case ALL = 'all';
}
