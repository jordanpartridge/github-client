<?php

use JordanPartridge\GithubClient\Data\Releases\ReleaseData;
use JordanPartridge\GithubClient\Facades\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('Releases functionality', function () {
    // Helper to create complete release mock data with all required fields
    beforeEach(function () {
        $this->createReleaseMock = function (array $overrides = []) {
            return array_merge([
                'url' => 'https://api.github.com/repos/owner/repo/releases/1',
                'assets_url' => 'https://api.github.com/repos/owner/repo/releases/1/assets',
                'upload_url' => 'https://uploads.github.com/repos/owner/repo/releases/1/assets{?name,label}',
                'html_url' => 'https://github.com/owner/repo/releases/tag/v1.0.0',
                'id' => 1,
                'author' => [
                    'login' => 'user',
                    'id' => 1,
                    'node_id' => 'MDQ6VXNlcjE=',
                    'avatar_url' => 'https://avatars.githubusercontent.com/u/1?v=4',
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
                    'site_admin' => false,
                ],
                'node_id' => 'MDc6UmVsZWFzZTE=',
                'tag_name' => 'v1.0.0',
                'target_commitish' => 'master',
                'name' => 'v1.0.0',
                'draft' => false,
                'prerelease' => false,
                'created_at' => '2024-01-01T00:00:00Z',
                'published_at' => '2024-01-01T00:00:00Z',
                'assets' => [],
                'tarball_url' => 'https://api.github.com/repos/owner/repo/tarball/v1.0.0',
                'zipball_url' => 'https://api.github.com/repos/owner/repo/zipball/v1.0.0',
                'body' => 'First release',
            ], $overrides);
        };
    });

    it('can list all releases for a repository', function () {
        $mockClient = new MockClient([
            MockResponse::make([
                ($this->createReleaseMock)([
                    'id' => 1,
                    'tag_name' => 'v1.0.0',
                    'name' => 'v1.0.0',
                    'body' => 'First release',
                ]),
                ($this->createReleaseMock)([
                    'id' => 2,
                    'tag_name' => 'v2.0.0',
                    'name' => 'v2.0.0',
                    'body' => 'Second release',
                    'url' => 'https://api.github.com/repos/owner/repo/releases/2',
                    'node_id' => 'MDc6UmVsZWFzZTI=',
                ]),
            ], 200),
        ]);

        Github::connector()->withMockClient($mockClient);

        $releases = Github::releases()->all('owner', 'repo');

        expect($releases)->toBeArray()
            ->toHaveCount(2)
            ->and($releases[0])->toBeInstanceOf(ReleaseData::class)
            ->and($releases[0]->tag_name)->toBe('v1.0.0')
            ->and($releases[0]->name)->toBe('v1.0.0')
            ->and($releases[0]->body)->toBe('First release')
            ->and($releases[1]->tag_name)->toBe('v2.0.0');
    });

    it('can get a specific release by ID', function () {
        $mockClient = new MockClient([
            MockResponse::make(
                ($this->createReleaseMock)([
                    'id' => 123456,
                    'tag_name' => 'v1.5.0',
                    'name' => 'Version 1.5.0',
                    'body' => '## Features\n- New feature X\n- Improved performance',
                    'url' => 'https://api.github.com/repos/owner/repo/releases/123456',
                    'html_url' => 'https://github.com/owner/repo/releases/tag/v1.5.0',
                    'assets' => [
                        [
                            'id' => 1,
                            'name' => 'release.zip',
                            'label' => 'Release Archive',
                            'size' => 1024000,
                            'download_count' => 42,
                            'created_at' => '2024-03-01T00:00:00Z',
                            'updated_at' => '2024-03-01T00:00:00Z',
                            'browser_download_url' => 'https://github.com/owner/repo/releases/download/v1.5.0/release.zip',
                        ],
                    ],
                ]),
                200,
            ),
        ]);

        Github::connector()->withMockClient($mockClient);

        $release = Github::releases()->get('owner', 'repo', 123456);

        expect($release)->toBeInstanceOf(ReleaseData::class)
            ->and($release->id)->toBe(123456)
            ->and($release->tag_name)->toBe('v1.5.0')
            ->and($release->name)->toBe('Version 1.5.0')
            ->and($release->body)->toContain('New feature X')
            ->and($release->assets)->toBeArray()
            ->toHaveCount(1)
            ->and($release->assets[0]['name'])->toBe('release.zip')
            ->and($release->assets[0]['download_count'])->toBe(42);
    });

    it('can get the latest release', function () {
        $mockClient = new MockClient([
            MockResponse::make(
                ($this->createReleaseMock)([
                    'id' => 999999,
                    'tag_name' => 'v3.0.0',
                    'target_commitish' => 'main',
                    'name' => 'Latest Stable Release',
                    'body' => '# Major Release\nBreaking changes included',
                    'url' => 'https://api.github.com/repos/owner/repo/releases/999999',
                    'html_url' => 'https://github.com/owner/repo/releases/tag/v3.0.0',
                    'node_id' => 'MDc6UmVsZWFzZTk5OTk5OQ==',
                    'author' => [
                        'login' => 'bot',
                        'id' => 3,
                        'node_id' => 'MDQ6VXNlcjM=',
                        'avatar_url' => 'https://avatars.githubusercontent.com/u/3?v=4',
                        'gravatar_id' => '',
                        'url' => 'https://api.github.com/users/bot',
                        'html_url' => 'https://github.com/bot',
                        'followers_url' => 'https://api.github.com/users/bot/followers',
                        'following_url' => 'https://api.github.com/users/bot/following{/other_user}',
                        'gists_url' => 'https://api.github.com/users/bot/gists{/gist_id}',
                        'starred_url' => 'https://api.github.com/users/bot/starred{/owner}{/repo}',
                        'subscriptions_url' => 'https://api.github.com/users/bot/subscriptions',
                        'organizations_url' => 'https://api.github.com/users/bot/orgs',
                        'repos_url' => 'https://api.github.com/users/bot/repos',
                        'events_url' => 'https://api.github.com/users/bot/events{/privacy}',
                        'received_events_url' => 'https://api.github.com/users/bot/received_events',
                        'type' => 'Bot',
                        'site_admin' => false,
                    ],
                ]),
                200,
            ),
        ]);

        Github::connector()->withMockClient($mockClient);

        $release = Github::releases()->latest('owner', 'repo');

        expect($release)->toBeInstanceOf(ReleaseData::class)
            ->and($release->tag_name)->toBe('v3.0.0')
            ->and($release->name)->toBe('Latest Stable Release')
            ->and($release->prerelease)->toBeFalse()
            ->and($release->draft)->toBeFalse()
            ->and($release->author->type)->toBe('Bot');
    });

    it('handles pagination parameters correctly', function () {
        $mockClient = new MockClient([
            MockResponse::make([
                ($this->createReleaseMock)([
                    'id' => 1,
                    'tag_name' => 'v1.0.0',
                    'name' => 'First',
                ]),
            ], 200),
        ]);

        Github::connector()->withMockClient($mockClient);

        $releases = Github::releases()->all('owner', 'repo', per_page: 1, page: 2);

        expect($releases)->toBeArray()
            ->toHaveCount(1)
            ->and($releases[0]->tag_name)->toBe('v1.0.0');
    });

    it('handles draft and prerelease flags correctly', function () {
        $mockClient = new MockClient([
            MockResponse::make(
                ($this->createReleaseMock)([
                    'id' => 100,
                    'tag_name' => 'v1.0.0-beta',
                    'name' => 'Beta Release',
                    'draft' => false,
                    'prerelease' => true,
                    'body' => 'Beta testing release',
                ]),
                200,
            ),
        ]);

        Github::connector()->withMockClient($mockClient);

        $release = Github::releases()->get('owner', 'repo', 100);

        expect($release->prerelease)->toBeTrue()
            ->and($release->draft)->toBeFalse()
            ->and($release->tag_name)->toContain('beta');
    });

    it('handles optional fields correctly', function () {
        $mockClient = new MockClient([
            MockResponse::make(
                ($this->createReleaseMock)([
                    'id' => 200,
                    'tag_name' => 'v2.0.0',
                    'name' => 'Minimal Release',
                    'body' => null,
                    'discussion_url' => null,
                    'reactions' => null,
                    'make_latest' => null,
                ]),
                200,
            ),
        ]);

        Github::connector()->withMockClient($mockClient);

        $release = Github::releases()->get('owner', 'repo', 200);

        expect($release)->toBeInstanceOf(ReleaseData::class)
            ->and($release->body)->toBeNull()
            ->and($release->discussion_url)->toBeNull()
            ->and($release->reactions)->toBeNull()
            ->and($release->make_latest)->toBeNull();
    });

    it('correctly converts ReleaseData to array', function () {
        $releaseData = ReleaseData::fromArray([
            'url' => 'https://api.github.com/repos/owner/repo/releases/123',
            'assets_url' => 'https://api.github.com/repos/owner/repo/releases/123/assets',
            'upload_url' => 'https://uploads.github.com/repos/owner/repo/releases/123/assets{?name,label}',
            'html_url' => 'https://github.com/owner/repo/releases/tag/v1.0.0',
            'id' => 123,
            'author' => [
                'login' => 'user',
                'id' => 1,
                'node_id' => 'MDQ6VXNlcjE=',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/1?v=4',
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
                'site_admin' => false,
            ],
            'node_id' => 'MDc6UmVsZWFzZTEyMw==',
            'tag_name' => 'v1.0.0',
            'target_commitish' => 'master',
            'name' => 'Test Release',
            'draft' => false,
            'prerelease' => false,
            'created_at' => '2024-01-01T00:00:00Z',
            'published_at' => '2024-01-01T00:00:00Z',
            'assets' => [],
            'tarball_url' => 'https://example.com/tarball',
            'zipball_url' => 'https://example.com/zipball',
            'body' => 'Release notes',
        ]);

        $array = $releaseData->toArray();

        expect($array)->toBeArray()
            ->toHaveKey('id', 123)
            ->toHaveKey('tag_name', 'v1.0.0')
            ->toHaveKey('name', 'Test Release')
            ->toHaveKey('body', 'Release notes')
            ->toHaveKey('draft', false)
            ->toHaveKey('prerelease', false);
    });

    it('handles releases with multiple assets', function () {
        $mockClient = new MockClient([
            MockResponse::make(
                ($this->createReleaseMock)([
                    'id' => 300,
                    'tag_name' => 'v3.0.0',
                    'name' => 'Multi-Asset Release',
                    'body' => 'Multi-platform release',
                    'assets' => [
                        [
                            'id' => 1,
                            'name' => 'app-linux.tar.gz',
                            'size' => 5000000,
                            'download_count' => 100,
                        ],
                        [
                            'id' => 2,
                            'name' => 'app-windows.zip',
                            'size' => 6000000,
                            'download_count' => 200,
                        ],
                        [
                            'id' => 3,
                            'name' => 'app-macos.dmg',
                            'size' => 7000000,
                            'download_count' => 150,
                        ],
                    ],
                ]),
                200,
            ),
        ]);

        Github::connector()->withMockClient($mockClient);

        $release = Github::releases()->get('owner', 'repo', 300);

        expect($release->assets)->toBeArray()
            ->toHaveCount(3)
            ->and($release->assets[0]['name'])->toBe('app-linux.tar.gz')
            ->and($release->assets[1]['name'])->toBe('app-windows.zip')
            ->and($release->assets[2]['name'])->toBe('app-macos.dmg')
            ->and(array_sum(array_column($release->assets, 'download_count')))->toBe(450);
    });

    it('validates all endpoint methods work correctly', function () {
        // Test all() endpoint
        $mockClient = new MockClient([
            MockResponse::make([], 200),
        ]);
        Github::connector()->withMockClient($mockClient);
        $releases = Github::releases()->all('owner', 'repo');
        expect($releases)->toBeArray();

        // Test get() endpoint
        $mockClient = new MockClient([
            MockResponse::make(($this->createReleaseMock)(['id' => 123]), 200),
        ]);
        Github::connector()->withMockClient($mockClient);
        $release = Github::releases()->get('owner', 'repo', 123);
        expect($release)->toBeInstanceOf(ReleaseData::class)
            ->and($release->id)->toBe(123);

        // Test latest() endpoint
        $mockClient = new MockClient([
            MockResponse::make(($this->createReleaseMock)(['tag_name' => 'v9.9.9']), 200),
        ]);
        Github::connector()->withMockClient($mockClient);
        $latest = Github::releases()->latest('owner', 'repo');
        expect($latest)->toBeInstanceOf(ReleaseData::class)
            ->and($latest->tag_name)->toBe('v9.9.9');
    });
});
