<?php

namespace JordanPartridge\GithubClient\Data;

class GitUserData
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

    public static function fromArray(array $data): self
    {
        return new self(
            login: $data['login'],
            id: $data['id'],
            node_id: $data['node_id'],
            avatar_url: $data['avatar_url'],
            gravatar_id: $data['gravatar_id'] ?? '',
            url: $data['url'],
            html_url: $data['html_url'],
            followers_url: $data['followers_url'],
            following_url: $data['following_url'],
            gists_url: $data['gists_url'],
            starred_url: $data['starred_url'],
            subscriptions_url: $data['subscriptions_url'],
            organizations_url: $data['organizations_url'],
            repos_url: $data['repos_url'],
            events_url: $data['events_url'],
            received_events_url: $data['received_events_url'],
            type: $data['type'],
            user_view_type: $data['user_view_type'] ?? '',
            site_admin: $data['site_admin'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'id' => $this->id,
            'node_id' => $this->node_id,
            'avatar_url' => $this->avatar_url,
            'gravatar_id' => $this->gravatar_id,
            'url' => $this->url,
            'html_url' => $this->html_url,
            'followers_url' => $this->followers_url,
            'following_url' => $this->following_url,
            'gists_url' => $this->gists_url,
            'starred_url' => $this->starred_url,
            'subscriptions_url' => $this->subscriptions_url,
            'organizations_url' => $this->organizations_url,
            'repos_url' => $this->repos_url,
            'events_url' => $this->events_url,
            'received_events_url' => $this->received_events_url,
            'type' => $this->type,
            'user_view_type' => $this->user_view_type,
            'site_admin' => $this->site_admin,
        ];
    }
}
