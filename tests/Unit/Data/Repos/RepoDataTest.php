<?php

use Carbon\Carbon;
use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Repos\LicenseData;
use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Enums\Visibility;

it('can create RepoData from array', function () {
    $data = [
        'id' => 123,
        'node_id' => 'MDEwOlJlcG9zaXRvcnkxMjM=',
        'name' => 'test-repo',
        'full_name' => 'user/test-repo',
        'private' => false,
        'owner' => [
            'login' => 'user',
            'id' => 456,
            'node_id' => 'MDQ6VXNlcjQ1Ng==',
            'avatar_url' => 'https://github.com/images/error/user_happy.gif',
            'gravatar_id' => '',
            'url' => 'https://api.github.com/users/user',
            'html_url' => 'https://github.com/user',
            'followers_url' => 'https://api.github.com/users/user/followers',
            'following_url' => 'https://api.github.com/users/user/following{/other_user}',
            'gists_url' => 'https://api.github.com/users/user/gists{/gist_id}',
            'starred_url' => 'https://api.github.com/users/user/starred{/owner}{/repo}',
            'subscriptions_url' => 'https://api.github.com/users/user/subscriptions',
            'organizations_url' => 'https://api.github.com/users/user/orgs',
            'repos_url' => 'https://api.github.com/users/user/repos',
            'events_url' => 'https://api.github.com/users/user/events{/privacy}',
            'received_events_url' => 'https://api.github.com/users/user/received_events',
            'type' => 'User',
            'user_view_type' => 'public',
            'site_admin' => false,
        ],
        'html_url' => 'https://github.com/user/test-repo',
        'description' => 'A test repository',
        'fork' => false,
        'url' => 'https://api.github.com/repos/user/test-repo',
        'created_at' => '2011-01-26T19:01:12Z',
        'updated_at' => '2024-01-26T19:14:43Z',
        'pushed_at' => '2024-01-26T19:14:43Z',
        'clone_url' => 'https://github.com/user/test-repo.git',
        'stargazers_count' => 80,
        'language' => 'PHP',
        'default_branch' => 'main',
        // Add minimal required fields with defaults
        'forks_url' => 'https://api.github.com/repos/user/test-repo/forks',
        'keys_url' => 'https://api.github.com/repos/user/test-repo/keys{/key_id}',
        'collaborators_url' => 'https://api.github.com/repos/user/test-repo/collaborators{/collaborator}',
        'teams_url' => 'https://api.github.com/repos/user/test-repo/teams',
        'hooks_url' => 'https://api.github.com/repos/user/test-repo/hooks',
        'issue_events_url' => 'https://api.github.com/repos/user/test-repo/issues/events{/number}',
        'events_url' => 'https://api.github.com/repos/user/test-repo/events',
        'assignees_url' => 'https://api.github.com/repos/user/test-repo/assignees{/user}',
        'branches_url' => 'https://api.github.com/repos/user/test-repo/branches{/branch}',
        'tags_url' => 'https://api.github.com/repos/user/test-repo/tags',
        'blobs_url' => 'https://api.github.com/repos/user/test-repo/git/blobs{/sha}',
        'git_tags_url' => 'https://api.github.com/repos/user/test-repo/git/tags{/sha}',
        'git_refs_url' => 'https://api.github.com/repos/user/test-repo/git/refs{/sha}',
        'trees_url' => 'https://api.github.com/repos/user/test-repo/git/trees{/sha}',
        'statuses_url' => 'https://api.github.com/repos/user/test-repo/statuses/{sha}',
        'languages_url' => 'https://api.github.com/repos/user/test-repo/languages',
        'stargazers_url' => 'https://api.github.com/repos/user/test-repo/stargazers',
        'contributors_url' => 'https://api.github.com/repos/user/test-repo/contributors',
        'subscribers_url' => 'https://api.github.com/repos/user/test-repo/subscribers',
        'subscription_url' => 'https://api.github.com/repos/user/test-repo/subscription',
        'commits_url' => 'https://api.github.com/repos/user/test-repo/commits{/sha}',
        'git_commits_url' => 'https://api.github.com/repos/user/test-repo/git/commits{/sha}',
        'comments_url' => 'https://api.github.com/repos/user/test-repo/comments{/number}',
        'issue_comment_url' => 'https://api.github.com/repos/user/test-repo/issues/comments{/number}',
        'contents_url' => 'https://api.github.com/repos/user/test-repo/contents/{+path}',
        'compare_url' => 'https://api.github.com/repos/user/test-repo/compare/{base}...{head}',
        'merges_url' => 'https://api.github.com/repos/user/test-repo/merges',
        'archive_url' => 'https://api.github.com/repos/user/test-repo/{archive_format}{/ref}',
        'downloads_url' => 'https://api.github.com/repos/user/test-repo/downloads',
        'issues_url' => 'https://api.github.com/repos/user/test-repo/issues{/number}',
        'pulls_url' => 'https://api.github.com/repos/user/test-repo/pulls{/number}',
        'milestones_url' => 'https://api.github.com/repos/user/test-repo/milestones{/number}',
        'notifications_url' => 'https://api.github.com/repos/user/test-repo/notifications{?since,all,participating}',
        'labels_url' => 'https://api.github.com/repos/user/test-repo/labels{/name}',
        'releases_url' => 'https://api.github.com/repos/user/test-repo/releases{/id}',
        'deployments_url' => 'https://api.github.com/repos/user/test-repo/deployments',
        'git_url' => 'git://github.com/user/test-repo.git',
        'ssh_url' => 'git@github.com:user/test-repo.git',
        'svn_url' => 'https://github.com/user/test-repo',
        'size' => 108,
        'watchers_count' => 80,
        'has_issues' => true,
        'has_projects' => true,
        'has_downloads' => true,
        'has_wiki' => true,
        'has_pages' => false,
        'has_discussions' => false,
        'forks_count' => 9,
        'archived' => false,
        'disabled' => false,
        'open_issues_count' => 0,
        'allow_forking' => true,
        'is_template' => false,
        'web_commit_signoff_required' => false,
        'topics' => [],
        'forks' => 9,
        'open_issues' => 0,
        'watchers' => 80,
        'permissions' => [
            'admin' => false,
            'maintain' => false,
            'push' => false,
            'triage' => false,
            'pull' => true,
        ],
    ];

    $repo = RepoData::fromArray($data);

    expect($repo->id)->toBe(123);
    expect($repo->name)->toBe('test-repo');
    expect($repo->full_name)->toBe('user/test-repo');
    expect($repo->private)->toBeFalse();
    expect($repo->description)->toBe('A test repository');
    expect($repo->stargazers_count)->toBe(80);
    expect($repo->language)->toBe('PHP');
    expect($repo->owner)->toBeInstanceOf(GitUserData::class);
    expect($repo->owner->login)->toBe('user');
});

it('can convert RepoData to array', function () {
    $userData = new GitUserData(
        login: 'user',
        id: 456,
        node_id: 'MDQ6VXNlcjQ1Ng==',
        avatar_url: 'https://github.com/images/error/user_happy.gif',
        gravatar_id: '',
        url: 'https://api.github.com/users/user',
        html_url: 'https://github.com/user',
        followers_url: 'https://api.github.com/users/user/followers',
        following_url: 'https://api.github.com/users/user/following{/other_user}',
        gists_url: 'https://api.github.com/users/user/gists{/gist_id}',
        starred_url: 'https://api.github.com/users/user/starred{/owner}{/repo}',
        subscriptions_url: 'https://api.github.com/users/user/subscriptions',
        organizations_url: 'https://api.github.com/users/user/orgs',
        repos_url: 'https://api.github.com/users/user/repos',
        events_url: 'https://api.github.com/users/user/events{/privacy}',
        received_events_url: 'https://api.github.com/users/user/received_events',
        type: 'User',
        user_view_type: 'public',
        site_admin: false,
    );

    $repo = new RepoData(
        id: 123,
        node_id: 'MDEwOlJlcG9zaXRvcnkxMjM=',
        name: 'test-repo',
        full_name: 'user/test-repo',
        private: false,
        owner: $userData,
        html_url: 'https://github.com/user/test-repo',
        description: 'A test repository',
        fork: false,
        url: 'https://api.github.com/repos/user/test-repo',
        forks_url: 'https://api.github.com/repos/user/test-repo/forks',
        keys_url: 'https://api.github.com/repos/user/test-repo/keys{/key_id}',
        collaborators_url: 'https://api.github.com/repos/user/test-repo/collaborators{/collaborator}',
        teams_url: 'https://api.github.com/repos/user/test-repo/teams',
        hooks_url: 'https://api.github.com/repos/user/test-repo/hooks',
        issue_events_url: 'https://api.github.com/repos/user/test-repo/issues/events{/number}',
        events_url: 'https://api.github.com/repos/user/test-repo/events',
        assignees_url: 'https://api.github.com/repos/user/test-repo/assignees{/user}',
        branches_url: 'https://api.github.com/repos/user/test-repo/branches{/branch}',
        tags_url: 'https://api.github.com/repos/user/test-repo/tags',
        blobs_url: 'https://api.github.com/repos/user/test-repo/git/blobs{/sha}',
        git_tags_url: 'https://api.github.com/repos/user/test-repo/git/tags{/sha}',
        git_refs_url: 'https://api.github.com/repos/user/test-repo/git/refs{/sha}',
        trees_url: 'https://api.github.com/repos/user/test-repo/git/trees{/sha}',
        statuses_url: 'https://api.github.com/repos/user/test-repo/statuses/{sha}',
        languages_url: 'https://api.github.com/repos/user/test-repo/languages',
        stargazers_url: 'https://api.github.com/repos/user/test-repo/stargazers',
        contributors_url: 'https://api.github.com/repos/user/test-repo/contributors',
        subscribers_url: 'https://api.github.com/repos/user/test-repo/subscribers',
        subscription_url: 'https://api.github.com/repos/user/test-repo/subscription',
        commits_url: 'https://api.github.com/repos/user/test-repo/commits{/sha}',
        git_commits_url: 'https://api.github.com/repos/user/test-repo/git/commits{/sha}',
        comments_url: 'https://api.github.com/repos/user/test-repo/comments{/number}',
        issue_comment_url: 'https://api.github.com/repos/user/test-repo/issues/comments{/number}',
        contents_url: 'https://api.github.com/repos/user/test-repo/contents/{+path}',
        compare_url: 'https://api.github.com/repos/user/test-repo/compare/{base}...{head}',
        merges_url: 'https://api.github.com/repos/user/test-repo/merges',
        archive_url: 'https://api.github.com/repos/user/test-repo/{archive_format}{/ref}',
        downloads_url: 'https://api.github.com/repos/user/test-repo/downloads',
        issues_url: 'https://api.github.com/repos/user/test-repo/issues{/number}',
        pulls_url: 'https://api.github.com/repos/user/test-repo/pulls{/number}',
        milestones_url: 'https://api.github.com/repos/user/test-repo/milestones{/number}',
        notifications_url: 'https://api.github.com/repos/user/test-repo/notifications{?since,all,participating}',
        labels_url: 'https://api.github.com/repos/user/test-repo/labels{/name}',
        releases_url: 'https://api.github.com/repos/user/test-repo/releases{/id}',
        deployments_url: 'https://api.github.com/repos/user/test-repo/deployments',
        created_at: \Carbon\Carbon::parse('2011-01-26T19:01:12Z'),
        updated_at: \Carbon\Carbon::parse('2024-01-26T19:14:43Z'),
        pushed_at: \Carbon\Carbon::parse('2024-01-26T19:14:43Z'),
        git_url: 'git://github.com/user/test-repo.git',
        ssh_url: 'git@github.com:user/test-repo.git',
        clone_url: 'https://github.com/user/test-repo.git',
        svn_url: 'https://github.com/user/test-repo',
        homepage: null,
        size: 108,
        stargazers_count: 80,
        watchers_count: 80,
        language: 'PHP',
        has_issues: true,
        has_projects: true,
        has_downloads: true,
        has_wiki: true,
        has_pages: false,
        has_discussions: false,
        forks_count: 9,
        mirror_url: null,
        archived: false,
        disabled: false,
        open_issues_count: 0,
        license: null,
        allow_forking: true,
        is_template: false,
        web_commit_signoff_required: false,
        topics: [],
        visibility: null,
        forks: 9,
        open_issues: 0,
        watchers: 80,
        default_branch: 'main',
        permissions: [
            'admin' => false,
            'maintain' => false,
            'push' => false,
            'triage' => false,
            'pull' => true,
        ],
    );

    $array = $repo->toArray();

    expect($array['id'])->toBe(123);
    expect($array['name'])->toBe('test-repo');
    expect($array['full_name'])->toBe('user/test-repo');
    expect($array['private'])->toBeFalse();
    expect($array['description'])->toBe('A test repository');
    expect($array['stargazers_count'])->toBe(80);
    expect($array['language'])->toBe('PHP');
    expect($array['owner'])->toBeArray();
    expect($array['owner']['login'])->toBe('user');
});

it('handles null description', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    unset($data['description']);

    $repo = RepoData::fromArray($data);

    expect($repo->description)->toBeNull();
});

it('handles visibility enum', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    $data['visibility'] = 'private';

    $repo = RepoData::fromArray($data);

    expect($repo->visibility)->toBe(Visibility::PRIVATE);
});

it('handles license data', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    $data['license'] = [
        'key' => 'mit',
        'name' => 'MIT License',
        'spdx_id' => 'MIT',
        'url' => 'https://api.github.com/licenses/mit',
        'node_id' => 'MDc6TGljZW5zZW1pdA==',
    ];

    $repo = RepoData::fromArray($data);

    expect($repo->license)->toBeInstanceOf(LicenseData::class);
    expect($repo->license->key)->toBe('mit');
});

it('handles topics array', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    $data['topics'] = ['php', 'laravel', 'github-api'];

    $repo = RepoData::fromArray($data);

    expect($repo->topics)->toBe(['php', 'laravel', 'github-api']);
});

it('handles private repository', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    $data['private'] = true;

    $repo = RepoData::fromArray($data);

    expect($repo->private)->toBeTrue();
});

it('handles archived repository', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    $data['archived'] = true;

    $repo = RepoData::fromArray($data);

    expect($repo->archived)->toBeTrue();
});

it('handles fork repository', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    $data['fork'] = true;

    $repo = RepoData::fromArray($data);

    expect($repo->fork)->toBeTrue();
});

it('parses Carbon dates correctly', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');

    $repo = RepoData::fromArray($data);

    expect($repo->created_at)->toBeInstanceOf(Carbon::class);
    expect($repo->updated_at)->toBeInstanceOf(Carbon::class);
    expect($repo->pushed_at)->toBeInstanceOf(Carbon::class);
});

it('handles missing permissions', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    unset($data['permissions']);

    $repo = RepoData::fromArray($data);

    expect($repo->permissions)->toBe([]);
});

it('handles is_template flag', function () {
    $data = $this->createMockRepoData('test-repo', 1, 'testuser');
    $data['is_template'] = true;

    $repo = RepoData::fromArray($data);

    expect($repo->is_template)->toBeTrue();
});
