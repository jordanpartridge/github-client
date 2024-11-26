<?php

namespace JordanPartridge\GithubClient\Data;
readonly class CommitAuthorDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $date,
    ) {}
}
