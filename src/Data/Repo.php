<?php

namespace JordanPartridge\GithubClient\Data;

class Repo
{
    public function __construct(
        public readonly string $name,
        public readonly string $full_name,
        public readonly string $description,
        public readonly string $html_url,
        public readonly string $url,
        public readonly string $clone_url,
        public readonly string $ssh_url,
        public readonly string $svn_url,
        public readonly string $homepage,
        public readonly string $language,
        public readonly string $forks_count,
        public readonly string $stargazers_count,
        public readonly string $watchers_count,
        public readonly string $open_issues_count,
        public readonly string $default_branch,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly string $pushed_at,
        public readonly string $visibility,
        public readonly string $forks,
        public readonly string $open_issues,
        public readonly string $watchers,
        public readonly string $license,
        public readonly string $archived,
        public readonly string $disabled,
        public readonly string $allow_forking
    ){}

}
