<?php

use Carbon\Carbon;
use JordanPartridge\GithubClient\Data\Commits\CommitData;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\CommitResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);

    $this->mockCommitData = function (array $overrides = []) {
        $sha = $overrides['sha'] ?? '1234567890123456789012345678901234567890';
        $date = Carbon::now()->toIso8601String();

        return array_merge([
            'sha' => $sha,
            'node_id' => 'MDQ6QmxvYjE0ODMzNDY0NjpkZjE5N2EzZTgwMjhmN2E5ODM3MDc2M2ZlN2EzNWFlYjYzOTMxOGExOg==',
            'url' => "https://api.github.com/repos/owner/repo/git/commits/{$sha}",
            'html_url' => "https://github.com/owner/repo/commit/{$sha}",
            'comments_url' => "https://api.github.com/repos/owner/repo/commits/{$sha}/comments",
            'commit' => [
                'author' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'date' => $date,
                ],
                'committer' => [
                    'name' => 'GitHub',
                    'email' => 'noreply@github.com',
                    'date' => $date,
                ],
                'message' => $overrides['message'] ?? 'Test commit message',
                'tree' => [
                    'sha' => '0987654321098765432109876543210987654321',
                    'url' => 'https://api.github.com/repos/owner/repo/git/trees/0987654321098765432109876543210987654321',
                ],
                'url' => "https://api.github.com/repos/owner/repo/git/commits/{$sha}",
                'comment_count' => 0,
                'verification' => [
                    'verified' => true,
                    'reason' => 'valid',
                    'signature' => 'signature',
                    'payload' => 'payload',
                ],
            ],
            'author' => $this->createMockUserData('john', 1),
            'committer' => $this->createMockUserData('github', 2),
            'parents' => [],
            'stats' => [
                'additions' => 10,
                'deletions' => 5,
                'total' => 15,
            ],
            'files' => [],
        ], $overrides);
    };
});

describe('CommitResource comprehensive tests', function () {
    it('can access commits resource through Github facade', function () {
        $resource = Github::commits();

        expect($resource)->toBeInstanceOf(CommitResource::class);
    });

    describe('all method', function () {
        it('returns array of commits', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    ($this->mockCommitData)(['sha' => 'abc1234567890123456789012345678901234567']),
                    ($this->mockCommitData)(['sha' => 'def1234567890123456789012345678901234567']),
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $commits = Github::commits()->all('owner/repo');

            expect($commits)->toBeArray()->toHaveCount(2);
        });

        it('accepts pagination parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([($this->mockCommitData)()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $commits = Github::commits()->all('owner/repo', per_page: 50, page: 2);

            expect($commits)->toBeArray();
        });

        it('validates repository name format', function () {
            expect(fn () => Github::commits()->all('invalid-repo-name'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('handles empty commits list', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $commits = Github::commits()->all('owner/repo');

            expect($commits)->toBeArray()->toBeEmpty();
        });

        it('uses default pagination values', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([($this->mockCommitData)()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            // Default values: per_page: 100, page: 1
            $commits = Github::commits()->all('owner/repo');

            expect($commits)->toBeArray();
        });
    });

    describe('get method', function () {
        it('returns commit data for valid SHA', function () {
            $sha = '1234567890123456789012345678901234567890';
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockCommitData)(['sha' => $sha]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $commit = Github::commits()->get('owner/repo', $sha);

            expect($commit)->toBeInstanceOf(CommitData::class)
                ->and($commit->sha)->toBe($sha);
        });

        it('validates repository name format', function () {
            expect(fn () => Github::commits()->get('invalid', 'abc'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('handles commit with files', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockCommitData)([
                    'files' => [
                        [
                            'sha' => 'file123',
                            'filename' => 'src/test.php',
                            'status' => 'modified',
                            'additions' => 10,
                            'deletions' => 5,
                            'changes' => 15,
                            'blob_url' => 'https://github.com/owner/repo/blob/abc/src/test.php',
                            'raw_url' => 'https://github.com/owner/repo/raw/abc/src/test.php',
                            'contents_url' => 'https://api.github.com/repos/owner/repo/contents/src/test.php?ref=abc',
                        ],
                    ],
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $commit = Github::commits()->get(
                'owner/repo',
                '1234567890123456789012345678901234567890',
            );

            expect($commit->files)->toBeArray();
        });

        it('handles commit with stats', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockCommitData)([
                    'stats' => [
                        'additions' => 100,
                        'deletions' => 50,
                        'total' => 150,
                    ],
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $commit = Github::commits()->get(
                'owner/repo',
                '1234567890123456789012345678901234567890',
            );

            expect($commit->stats)->not->toBeNull();
        });

        it('handles verified commit', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockCommitData)(), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $commit = Github::commits()->get(
                'owner/repo',
                '1234567890123456789012345678901234567890',
            );

            expect($commit)->toBeInstanceOf(CommitData::class);
        });
    });

    describe('repository name validation', function () {
        it('accepts valid owner/repo format', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([($this->mockCommitData)()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $commits = Github::commits()->all('jordanpartridge/github-client');

            expect($commits)->toBeArray();
        });

        it('rejects missing slash', function () {
            expect(fn () => Github::commits()->all('invalidreponame'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('rejects empty owner', function () {
            expect(fn () => Github::commits()->all('/repo'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('rejects empty repo name', function () {
            expect(fn () => Github::commits()->all('owner/'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('uses ValidatesRepoName trait', function () {
        it('uses the trait', function () {
            $traits = class_uses(CommitResource::class);

            expect($traits)->toContain('JordanPartridge\GithubClient\Concerns\ValidatesRepoName');
        });
    });

    describe('Repo value object integration', function () {
        it('creates Repo value object from full name', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockCommitData)(), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            // This internally uses Repo::fromFullName
            $commit = Github::commits()->get(
                'jordanpartridge/github-client',
                '1234567890123456789012345678901234567890',
            );

            expect($commit)->toBeInstanceOf(CommitData::class);
        });
    });
});
