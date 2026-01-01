<?php

use JordanPartridge\GithubClient\Data\Releases\ReleaseData;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\ReleasesResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);

    $this->createReleaseMock = function (array $overrides = []) {
        return array_merge([
            'url' => 'https://api.github.com/repos/owner/repo/releases/1',
            'assets_url' => 'https://api.github.com/repos/owner/repo/releases/1/assets',
            'upload_url' => 'https://uploads.github.com/repos/owner/repo/releases/1/assets{?name,label}',
            'html_url' => 'https://github.com/owner/repo/releases/tag/v1.0.0',
            'id' => 1,
            'author' => $this->createMockUserData('releaser', 1),
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
            'body' => 'Release notes',
        ], $overrides);
    };
});

describe('ReleasesResource', function () {
    it('can access releases resource through Github facade', function () {
        $resource = Github::releases();

        expect($resource)->toBeInstanceOf(ReleasesResource::class);
    });

    describe('all method', function () {
        it('returns array of ReleaseData', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    ($this->createReleaseMock)(['id' => 1, 'tag_name' => 'v1.0.0']),
                    ($this->createReleaseMock)(['id' => 2, 'tag_name' => 'v2.0.0']),
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $releases = Github::releases()->all('owner', 'repo');

            expect($releases)
                ->toBeArray()
                ->toHaveCount(2)
                ->and($releases[0])->toBeInstanceOf(ReleaseData::class)
                ->and($releases[0]->tag_name)->toBe('v1.0.0')
                ->and($releases[1]->tag_name)->toBe('v2.0.0');
        });

        it('accepts pagination parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([($this->createReleaseMock)()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $releases = Github::releases()->all('owner', 'repo', per_page: 10, page: 2);

            expect($releases)->toBeArray();
        });

        it('handles empty releases list', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $releases = Github::releases()->all('owner', 'repo');

            expect($releases)
                ->toBeArray()
                ->toBeEmpty();
        });

        it('accepts null pagination parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([($this->createReleaseMock)()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $releases = Github::releases()->all('owner', 'repo', per_page: null, page: null);

            expect($releases)->toBeArray();
        });
    });

    describe('get method', function () {
        it('returns ReleaseData for valid release ID', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->createReleaseMock)([
                    'id' => 123456,
                    'tag_name' => 'v1.5.0',
                    'name' => 'Version 1.5.0',
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $release = Github::releases()->get('owner', 'repo', 123456);

            expect($release)
                ->toBeInstanceOf(ReleaseData::class)
                ->and($release->id)->toBe(123456)
                ->and($release->tag_name)->toBe('v1.5.0');
        });

        it('returns release with assets', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->createReleaseMock)([
                    'assets' => [
                        [
                            'id' => 1,
                            'name' => 'release.zip',
                            'size' => 1024000,
                            'download_count' => 42,
                        ],
                    ],
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $release = Github::releases()->get('owner', 'repo', 1);

            expect($release->assets)
                ->toBeArray()
                ->toHaveCount(1)
                ->and($release->assets[0]['name'])->toBe('release.zip');
        });

        it('handles draft release', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->createReleaseMock)([
                    'draft' => true,
                    'prerelease' => false,
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $release = Github::releases()->get('owner', 'repo', 1);

            expect($release->draft)->toBeTrue()
                ->and($release->prerelease)->toBeFalse();
        });

        it('handles prerelease', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->createReleaseMock)([
                    'draft' => false,
                    'prerelease' => true,
                    'tag_name' => 'v1.0.0-beta',
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $release = Github::releases()->get('owner', 'repo', 1);

            expect($release->prerelease)->toBeTrue()
                ->and($release->draft)->toBeFalse()
                ->and($release->tag_name)->toContain('beta');
        });
    });

    describe('latest method', function () {
        it('returns ReleaseData for latest release', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->createReleaseMock)([
                    'id' => 999,
                    'tag_name' => 'v3.0.0',
                    'name' => 'Latest Stable Release',
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $release = Github::releases()->latest('owner', 'repo');

            expect($release)
                ->toBeInstanceOf(ReleaseData::class)
                ->and($release->tag_name)->toBe('v3.0.0');
        });

        it('returns non-prerelease release', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->createReleaseMock)([
                    'prerelease' => false,
                    'draft' => false,
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $release = Github::releases()->latest('owner', 'repo');

            expect($release->prerelease)->toBeFalse()
                ->and($release->draft)->toBeFalse();
        });
    });

    describe('ReleaseData properties', function () {
        it('has all required properties', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->createReleaseMock)(), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $release = Github::releases()->get('owner', 'repo', 1);

            expect($release)
                ->toHaveProperty('id')
                ->toHaveProperty('tag_name')
                ->toHaveProperty('name')
                ->toHaveProperty('body')
                ->toHaveProperty('draft')
                ->toHaveProperty('prerelease')
                ->toHaveProperty('created_at')
                ->toHaveProperty('published_at')
                ->toHaveProperty('assets')
                ->toHaveProperty('author');
        });

        it('handles null body', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->createReleaseMock)([
                    'body' => null,
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $release = Github::releases()->get('owner', 'repo', 1);

            expect($release->body)->toBeNull();
        });
    });

    describe('different repository formats', function () {
        it('works with standard owner/repo format', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([($this->createReleaseMock)()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $releases = Github::releases()->all('jordanpartridge', 'github-client');

            expect($releases)->toBeArray();
        });

        it('works with organization repositories', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([($this->createReleaseMock)()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $releases = Github::releases()->all('laravel', 'framework');

            expect($releases)->toBeArray();
        });
    });
});
