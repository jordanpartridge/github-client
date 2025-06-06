<?php

namespace JordanPartridge\GithubClient\Data;

use Spatie\LaravelData\Data;

class GitUserData extends Data
{
    public function __construct(
        public string $login,
        public int $id,
        public string $node_id,
        public string $avatar_url,
        public string $gravatar_id,
        public string $url,
        public string $html_url,
        public string $followers_url,
        public string $following_url,
        public string $gists_url,
        public string $starred_url,
        public string $subscriptions_url,
        public string $organizations_url,
        public string $repos_url,
        public string $events_url,
        public string $received_events_url,
        public string $type,
        public string $user_view_type,
        public bool $site_admin,
    ) {}
}
