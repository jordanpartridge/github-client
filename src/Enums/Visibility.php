<?php

namespace JordanPartridge\GithubClient\Enums;

enum Visibility: string
{
    case PUBLIC = 'public';
    case PRIVATE  = 'private';
    case INTERNAL = 'internal';
}
