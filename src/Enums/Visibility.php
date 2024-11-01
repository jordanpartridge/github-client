<?php

namespace JordanPartridge\GithubClient\Enums;

enum Visibility
{
    case PUBLIC;
    case PRIVATE;
    case INTERNAL;

    public function toGithubString(): string
    {
        return match ($this) {
            self::PUBLIC => 'public',
            self::PRIVATE => 'private',
            self::INTERNAL => 'internal',
        };
    }
}
