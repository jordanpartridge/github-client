<?php

namespace ShadeTheory\GithubClient\Contracts;

use ShadeTheory\GithubClient\Github;

interface ResourceInterface
{
    public function __construct(Github $github);
}
