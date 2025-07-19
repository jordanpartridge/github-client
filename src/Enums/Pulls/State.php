<?php

namespace JordanPartridge\GithubClient\Enums\Pulls;

enum State: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case ALL = 'all';
}
