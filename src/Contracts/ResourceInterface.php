<?php

namespace JordanPartridge\GithubClient\Contracts;

use JordanPartridge\GithubClient\Github;

interface ResourceInterface
{
    public function __construct(Github $github);
}
