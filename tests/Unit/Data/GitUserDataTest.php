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

it('handles missing gravatar_id with default', function () {
    $data = [
        'login' => 'testuser',
        'id' => 123,
        'node_id' => 'MDQ6VXNlcjEyMw==',
        'avatar_url' => 'https://github.com/testuser.png',
        'url' => 'https://api.github.com/users/testuser',
        'html_url' => 'https://github.com/testuser',
        'followers_url' => 'https://api.github.com/users/testuser/followers',
        'following_url' => 'https://api.github.com/users/testuser/following{/other_user}',
        'gists_url' => 'https://api.github.com/users/testuser/gists{/gist_id}',
        'starred_url' => 'https://api.github.com/users/testuser/starred{/owner}{/repo}',
        'subscriptions_url' => 'https://api.github.com/users/testuser/subscriptions',
        'organizations_url' => 'https://api.github.com/users/testuser/orgs',
        'repos_url' => 'https://api.github.com/users/testuser/repos',
        'events_url' => 'https://api.github.com/users/testuser/events{/privacy}',
        'received_events_url' => 'https://api.github.com/users/testuser/received_events',
        'type' => 'User',
    ];

    $user = GitUserData::fromArray($data);

    expect($user->gravatar_id)->toBe('');
});

it('handles missing user_view_type with default', function () {
    $data = [
        'login' => 'testuser',
        'id' => 123,
        'node_id' => 'MDQ6VXNlcjEyMw==',
        'avatar_url' => 'https://github.com/testuser.png',
        'gravatar_id' => '',
        'url' => 'https://api.github.com/users/testuser',
        'html_url' => 'https://github.com/testuser',
        'followers_url' => 'https://api.github.com/users/testuser/followers',
        'following_url' => 'https://api.github.com/users/testuser/following{/other_user}',
        'gists_url' => 'https://api.github.com/users/testuser/gists{/gist_id}',
        'starred_url' => 'https://api.github.com/users/testuser/starred{/owner}{/repo}',
        'subscriptions_url' => 'https://api.github.com/users/testuser/subscriptions',
        'organizations_url' => 'https://api.github.com/users/testuser/orgs',
        'repos_url' => 'https://api.github.com/users/testuser/repos',
        'events_url' => 'https://api.github.com/users/testuser/events{/privacy}',
        'received_events_url' => 'https://api.github.com/users/testuser/received_events',
        'type' => 'User',
    ];

    $user = GitUserData::fromArray($data);

    expect($user->user_view_type)->toBe('');
});

it('handles missing site_admin with default', function () {
    $data = [
        'login' => 'testuser',
        'id' => 123,
        'node_id' => 'MDQ6VXNlcjEyMw==',
        'avatar_url' => 'https://github.com/testuser.png',
        'gravatar_id' => '',
        'url' => 'https://api.github.com/users/testuser',
        'html_url' => 'https://github.com/testuser',
        'followers_url' => 'https://api.github.com/users/testuser/followers',
        'following_url' => 'https://api.github.com/users/testuser/following{/other_user}',
        'gists_url' => 'https://api.github.com/users/testuser/gists{/gist_id}',
        'starred_url' => 'https://api.github.com/users/testuser/starred{/owner}{/repo}',
        'subscriptions_url' => 'https://api.github.com/users/testuser/subscriptions',
        'organizations_url' => 'https://api.github.com/users/testuser/orgs',
        'repos_url' => 'https://api.github.com/users/testuser/repos',
        'events_url' => 'https://api.github.com/users/testuser/events{/privacy}',
        'received_events_url' => 'https://api.github.com/users/testuser/received_events',
        'type' => 'User',
    ];

    $user = GitUserData::fromArray($data);

    expect($user->site_admin)->toBeFalse();
});

it('handles Organization type', function () {
    $data = [
        'login' => 'github',
        'id' => 9919,
        'node_id' => 'MDEyOk9yZ2FuaXphdGlvbjk5MTk=',
        'avatar_url' => 'https://github.com/github.png',
        'gravatar_id' => '',
        'url' => 'https://api.github.com/orgs/github',
        'html_url' => 'https://github.com/github',
        'followers_url' => 'https://api.github.com/users/github/followers',
        'following_url' => 'https://api.github.com/users/github/following{/other_user}',
        'gists_url' => 'https://api.github.com/users/github/gists{/gist_id}',
        'starred_url' => 'https://api.github.com/users/github/starred{/owner}{/repo}',
        'subscriptions_url' => 'https://api.github.com/users/github/subscriptions',
        'organizations_url' => 'https://api.github.com/users/github/orgs',
        'repos_url' => 'https://api.github.com/users/github/repos',
        'events_url' => 'https://api.github.com/users/github/events{/privacy}',
        'received_events_url' => 'https://api.github.com/users/github/received_events',
        'type' => 'Organization',
        'site_admin' => false,
    ];

    $user = GitUserData::fromArray($data);

    expect($user->type)->toBe('Organization');
});

it('handles Bot type', function () {
    $data = [
        'login' => 'dependabot[bot]',
        'id' => 49699333,
        'node_id' => 'MDM6Qm90NDk2OTkzMzM=',
        'avatar_url' => 'https://github.com/dependabot.png',
        'gravatar_id' => '',
        'url' => 'https://api.github.com/users/dependabot%5Bbot%5D',
        'html_url' => 'https://github.com/apps/dependabot',
        'followers_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/followers',
        'following_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/following{/other_user}',
        'gists_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/gists{/gist_id}',
        'starred_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/starred{/owner}{/repo}',
        'subscriptions_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/subscriptions',
        'organizations_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/orgs',
        'repos_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/repos',
        'events_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/events{/privacy}',
        'received_events_url' => 'https://api.github.com/users/dependabot%5Bbot%5D/received_events',
        'type' => 'Bot',
        'site_admin' => false,
    ];

    $user = GitUserData::fromArray($data);

    expect($user->type)->toBe('Bot');
    expect($user->login)->toBe('dependabot[bot]');
});

it('handles site admin user', function () {
    $data = [
        'login' => 'admin',
        'id' => 1,
        'node_id' => 'MDQ6VXNlcjE=',
        'avatar_url' => 'https://github.com/admin.png',
        'gravatar_id' => '',
        'url' => 'https://api.github.com/users/admin',
        'html_url' => 'https://github.com/admin',
        'followers_url' => 'https://api.github.com/users/admin/followers',
        'following_url' => 'https://api.github.com/users/admin/following{/other_user}',
        'gists_url' => 'https://api.github.com/users/admin/gists{/gist_id}',
        'starred_url' => 'https://api.github.com/users/admin/starred{/owner}{/repo}',
        'subscriptions_url' => 'https://api.github.com/users/admin/subscriptions',
        'organizations_url' => 'https://api.github.com/users/admin/orgs',
        'repos_url' => 'https://api.github.com/users/admin/repos',
        'events_url' => 'https://api.github.com/users/admin/events{/privacy}',
        'received_events_url' => 'https://api.github.com/users/admin/received_events',
        'type' => 'User',
        'site_admin' => true,
    ];

    $user = GitUserData::fromArray($data);

    expect($user->site_admin)->toBeTrue();
});

it('preserves all URL fields', function () {
    $user = new GitUserData(
        login: 'test',
        id: 1,
        node_id: 'node123',
        avatar_url: 'https://avatar.url',
        gravatar_id: 'gravatar123',
        url: 'https://api.url',
        html_url: 'https://html.url',
        followers_url: 'https://followers.url',
        following_url: 'https://following.url',
        gists_url: 'https://gists.url',
        starred_url: 'https://starred.url',
        subscriptions_url: 'https://subscriptions.url',
        organizations_url: 'https://organizations.url',
        repos_url: 'https://repos.url',
        events_url: 'https://events.url',
        received_events_url: 'https://received_events.url',
        type: 'User',
        user_view_type: 'public',
        site_admin: false,
    );

    $array = $user->toArray();

    expect($array['followers_url'])->toBe('https://followers.url');
    expect($array['following_url'])->toBe('https://following.url');
    expect($array['gists_url'])->toBe('https://gists.url');
    expect($array['starred_url'])->toBe('https://starred.url');
    expect($array['subscriptions_url'])->toBe('https://subscriptions.url');
    expect($array['organizations_url'])->toBe('https://organizations.url');
    expect($array['repos_url'])->toBe('https://repos.url');
    expect($array['events_url'])->toBe('https://events.url');
    expect($array['received_events_url'])->toBe('https://received_events.url');
});
