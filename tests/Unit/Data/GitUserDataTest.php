<?php

use JordanPartridge\GithubClient\Data\GitUserData;

it('can create GitUserData from array', function () {
    $data = [
        'login' => 'octocat',
        'id' => 1,
        'node_id' => 'MDQ6VXNlcjE=',
        'avatar_url' => 'https://github.com/images/error/octocat_happy.gif',
        'gravatar_id' => '',
        'url' => 'https://api.github.com/users/octocat',
        'html_url' => 'https://github.com/octocat',
        'followers_url' => 'https://api.github.com/users/octocat/followers',
        'following_url' => 'https://api.github.com/users/octocat/following{/other_user}',
        'gists_url' => 'https://api.github.com/users/octocat/gists{/gist_id}',
        'starred_url' => 'https://api.github.com/users/octocat/starred{/owner}{/repo}',
        'subscriptions_url' => 'https://api.github.com/users/octocat/subscriptions',
        'organizations_url' => 'https://api.github.com/users/octocat/orgs',
        'repos_url' => 'https://api.github.com/users/octocat/repos',
        'events_url' => 'https://api.github.com/users/octocat/events{/privacy}',
        'received_events_url' => 'https://api.github.com/users/octocat/received_events',
        'type' => 'User',
        'user_view_type' => 'public',
        'site_admin' => false,
    ];

    $user = GitUserData::fromArray($data);

    expect($user->login)->toBe('octocat');
    expect($user->id)->toBe(1);
    expect($user->type)->toBe('User');
    expect($user->site_admin)->toBeFalse();
});

it('can convert GitUserData to array', function () {
    $user = new GitUserData(
        login: 'octocat',
        id: 1,
        node_id: 'MDQ6VXNlcjE=',
        avatar_url: 'https://github.com/images/error/octocat_happy.gif',
        gravatar_id: '',
        url: 'https://api.github.com/users/octocat',
        html_url: 'https://github.com/octocat',
        followers_url: 'https://api.github.com/users/octocat/followers',
        following_url: 'https://api.github.com/users/octocat/following{/other_user}',
        gists_url: 'https://api.github.com/users/octocat/gists{/gist_id}',
        starred_url: 'https://api.github.com/users/octocat/starred{/owner}{/repo}',
        subscriptions_url: 'https://api.github.com/users/octocat/subscriptions',
        organizations_url: 'https://api.github.com/users/octocat/orgs',
        repos_url: 'https://api.github.com/users/octocat/repos',
        events_url: 'https://api.github.com/users/octocat/events{/privacy}',
        received_events_url: 'https://api.github.com/users/octocat/received_events',
        type: 'User',
        user_view_type: 'public',
        site_admin: false,
    );

    $array = $user->toArray();

    expect($array['login'])->toBe('octocat');
    expect($array['id'])->toBe(1);
    expect($array['type'])->toBe('User');
    expect($array['site_admin'])->toBeFalse();
    expect($array)->toHaveKey('avatar_url');
    expect($array)->toHaveKey('url');
});