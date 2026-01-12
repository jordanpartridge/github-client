<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDetailDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestFileDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestSummaryDTO;
use JordanPartridge\GithubClient\Enums\MergeMethod;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);

    $this->mockPRData = function (array $overrides = []) {
        return array_merge([
            'id' => 1,
            'number' => 1,
            'state' => 'open',
            'title' => 'Test Pull Request',
            'body' => 'This is a test pull request',
            'html_url' => 'https://github.com/test/repo/pull/1',
            'diff_url' => 'https://github.com/test/repo/pull/1.diff',
            'patch_url' => 'https://github.com/test/repo/pull/1.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature-branch'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'comments' => 5,
            'review_comments' => 3,
            'commits' => 2,
            'additions' => 10,
            'deletions' => 5,
            'changed_files' => 2,
            'user' => $this->createMockUserData('testuser', 1),
            'merged_by' => null,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'closed_at' => null,
        ], $overrides);
    };
});

describe('PullRequestResource', function () {
    it('can access pullRequests resource through Github facade', function () {
        $resource = Github::pullRequests();

        expect($resource)->toBeInstanceOf(PullRequestResource::class);
    });

    describe('getComment method', function () {
        it('can get a single comment by ID', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'id' => 123,
                    'node_id' => 'abc123',
                    'path' => 'src/test.php',
                    'position' => 5,
                    'original_position' => 5,
                    'commit_id' => 'abc123def456',
                    'original_commit_id' => 'abc123def456',
                    'user' => $this->createMockUserData('commenter', 3),
                    'body' => 'Specific comment',
                    'html_url' => 'https://github.com/test/repo/pull/1#discussion_r123',
                    'pull_request_url' => 'https://api.github.com/repos/test/repo/pulls/1',
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-01T00:00:00Z',
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comment = Github::pullRequests()->getComment('test', 'repo', 123);

            expect($comment)
                ->toBeInstanceOf(PullRequestCommentDTO::class)
                ->and($comment->id)->toBe(123);
        });
    });

    describe('updateComment method', function () {
        it('can update a comment', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'id' => 123,
                    'node_id' => 'abc123',
                    'path' => 'src/test.php',
                    'position' => 5,
                    'original_position' => 5,
                    'commit_id' => 'abc123def456',
                    'original_commit_id' => 'abc123def456',
                    'user' => $this->createMockUserData('commenter', 3),
                    'body' => 'Updated comment body',
                    'html_url' => 'https://github.com/test/repo/pull/1#discussion_r123',
                    'pull_request_url' => 'https://api.github.com/repos/test/repo/pulls/1',
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-02T00:00:00Z',
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comment = Github::pullRequests()->updateComment('test', 'repo', 123, 'Updated comment body');

            expect($comment)
                ->toBeInstanceOf(PullRequestCommentDTO::class)
                ->and($comment->body)->toBe('Updated comment body');
        });
    });

    describe('deleteComment method', function () {
        it('returns true on successful deletion', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 204),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::pullRequests()->deleteComment('test', 'repo', 123);

            expect($result)->toBeTrue();
        });

        it('returns false on failed deletion', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['message' => 'Not found'], 404),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::pullRequests()->deleteComment('test', 'repo', 999);

            expect($result)->toBeFalse();
        });
    });

    describe('commentsWithFilters method', function () {
        it('can filter comments by author', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([[
                    'id' => 1,
                    'node_id' => 'abc123',
                    'path' => 'src/test.php',
                    'position' => 5,
                    'original_position' => 5,
                    'commit_id' => 'abc123def456',
                    'original_commit_id' => 'abc123def456',
                    'user' => $this->createMockUserData('coderabbitai', 1),
                    'body' => 'AI review comment',
                    'html_url' => 'https://github.com/test/repo/pull/1#discussion_r1',
                    'pull_request_url' => 'https://api.github.com/repos/test/repo/pulls/1',
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-01T00:00:00Z',
                ]], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::pullRequests()->commentsWithFilters('test', 'repo', 1, [
                'author' => 'coderabbitai',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('forPullRequest method', function () {
        it('is an alias for commentsWithFilters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::pullRequests()->forPullRequest('test', 'repo', 1, [
                'author' => 'coderabbitai',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('summaries method', function () {
        it('returns array of PullRequestSummaryDTO', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([[
                    'id' => 1,
                    'number' => 1,
                    'state' => 'open',
                    'title' => 'Test PR',
                    'body' => 'Test body',
                    'html_url' => 'https://github.com/test/repo/pull/1',
                    'diff_url' => 'https://github.com/test/repo/pull/1.diff',
                    'patch_url' => 'https://github.com/test/repo/pull/1.patch',
                    'base' => ['ref' => 'main'],
                    'head' => ['ref' => 'feature'],
                    'draft' => false,
                    'merged' => false,
                    'merged_at' => null,
                    'merge_commit_sha' => null,
                    'user' => $this->createMockUserData(),
                    'merged_by' => null,
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-01T00:00:00Z',
                    'closed_at' => null,
                ]], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $summaries = Github::pullRequests()->summaries('test', 'repo');

            expect($summaries)
                ->toBeArray()
                ->and($summaries[0])->toBeInstanceOf(PullRequestSummaryDTO::class);
        });
    });

    describe('detail method', function () {
        it('returns PullRequestDetailDTO', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockPRData)(), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $detail = Github::pullRequests()->detail('test', 'repo', 1);

            expect($detail)->toBeInstanceOf(PullRequestDetailDTO::class);
        });
    });

    describe('detailsForMultiple method', function () {
        it('fetches details for multiple PRs', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockPRData)(), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $details = Github::pullRequests()->detailsForMultiple('test', 'repo', [1, 2, 3]);

            expect($details)
                ->toBeArray()
                ->toHaveCount(3);
        });

        it('respects maxRequests limit', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockPRData)(), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $details = Github::pullRequests()->detailsForMultiple(
                'test',
                'repo',
                [1, 2, 3, 4, 5],
                maxRequests: 2,
            );

            expect($details)->toHaveCount(2);
        });

        it('handles exceptions gracefully', function () {
            $callCount = 0;
            $mockClient = new MockClient([
                '*' => function () use (&$callCount) {
                    $callCount++;
                    if ($callCount === 2) {
                        return MockResponse::make(['message' => 'Not found'], 404);
                    }

                    return MockResponse::make(($this->mockPRData)(), 200);
                },
            ]);

            Github::connector()->withMockClient($mockClient);

            // This should not throw, just skip the failed PR
            $details = Github::pullRequests()->detailsForMultiple('test', 'repo', [1, 2, 3]);

            expect($details)->toBeArray();
        });
    });

    describe('recentDetails method', function () {
        it('fetches recent PRs with details', function () {
            $summaryResponse = MockResponse::make([[
                'id' => 1,
                'number' => 1,
                'state' => 'open',
                'title' => 'Test PR',
                'body' => 'Test body',
                'html_url' => 'https://github.com/test/repo/pull/1',
                'diff_url' => 'https://github.com/test/repo/pull/1.diff',
                'patch_url' => 'https://github.com/test/repo/pull/1.patch',
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature'],
                'draft' => false,
                'merged' => false,
                'merged_at' => null,
                'merge_commit_sha' => null,
                'user' => $this->createMockUserData(),
                'merged_by' => null,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'closed_at' => null,
            ]], 200);

            $detailResponse = MockResponse::make(($this->mockPRData)(), 200);

            $mockClient = new MockClient();
            $mockClient->addResponse($summaryResponse);
            $mockClient->addResponse($detailResponse);

            Github::connector()->withMockClient($mockClient);

            $details = Github::pullRequests()->recentDetails('test', 'repo', 5, 'open');

            expect($details)->toBeArray();
        });

        it('limits per_page to 10 for rate limit protection', function () {
            $summaries = [];
            for ($i = 1; $i <= 10; $i++) {
                $summaries[] = [
                    'id' => $i,
                    'number' => $i,
                    'state' => 'open',
                    'title' => "PR $i",
                    'body' => 'Test body',
                    'html_url' => "https://github.com/test/repo/pull/$i",
                    'diff_url' => "https://github.com/test/repo/pull/$i.diff",
                    'patch_url' => "https://github.com/test/repo/pull/$i.patch",
                    'base' => ['ref' => 'main'],
                    'head' => ['ref' => 'feature'],
                    'draft' => false,
                    'merged' => false,
                    'merged_at' => null,
                    'merge_commit_sha' => null,
                    'user' => $this->createMockUserData(),
                    'merged_by' => null,
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-01T00:00:00Z',
                    'closed_at' => null,
                ];
            }

            // First call returns summaries (max 10), subsequent calls return details
            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make($summaries, 200));
            for ($i = 0; $i < 10; $i++) {
                $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => $i + 1]), 200));
            }

            Github::connector()->withMockClient($mockClient);

            // Request 15 but per_page is capped at 10, so only 10 summaries returned
            $details = Github::pullRequests()->recentDetails('test', 'repo', 15);

            // Since per_page is min($limit, 10) = 10, only 10 summaries returned
            expect(count($details))->toBeLessThanOrEqual(10);
        });
    });

    describe('files method', function () {
        it('returns array of PullRequestFileDTO', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    [
                        'sha' => 'abc123',
                        'filename' => 'src/test.php',
                        'status' => 'modified',
                        'additions' => 10,
                        'deletions' => 5,
                        'changes' => 15,
                        'blob_url' => 'https://github.com/test/repo/blob/abc123/src/test.php',
                        'raw_url' => 'https://github.com/test/repo/raw/abc123/src/test.php',
                        'contents_url' => 'https://api.github.com/repos/test/repo/contents/src/test.php?ref=abc123',
                        'patch' => '@@ -1,5 +1,10 @@',
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $files = Github::pullRequests()->files('test', 'repo', 1);

            expect($files)
                ->toBeArray()
                ->and($files[0])->toBeInstanceOf(PullRequestFileDTO::class);
        });
    });

    describe('diff method', function () {
        it('returns analysis data with summary', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    [
                        'sha' => 'abc123',
                        'filename' => 'src/test.php',
                        'status' => 'modified',
                        'additions' => 10,
                        'deletions' => 5,
                        'changes' => 15,
                        'blob_url' => 'https://github.com/test/repo/blob/abc123/src/test.php',
                        'raw_url' => 'https://github.com/test/repo/raw/abc123/src/test.php',
                        'contents_url' => 'https://api.github.com/repos/test/repo/contents/src/test.php?ref=abc123',
                        'patch' => '@@ -1,5 +1,10 @@',
                    ],
                    [
                        'sha' => 'def456',
                        'filename' => 'tests/TestCase.php',
                        'status' => 'added',
                        'additions' => 50,
                        'deletions' => 0,
                        'changes' => 50,
                        'blob_url' => 'https://github.com/test/repo/blob/def456/tests/TestCase.php',
                        'raw_url' => 'https://github.com/test/repo/raw/def456/tests/TestCase.php',
                        'contents_url' => 'https://api.github.com/repos/test/repo/contents/tests/TestCase.php?ref=def456',
                        'patch' => '@@ -0,0 +1,50 @@',
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('test', 'repo', 1);

            expect($analysis)
                ->toBeArray()
                ->toHaveKeys(['summary', 'categories', 'files', 'analysis_tags'])
                ->and($analysis['summary'])->toHaveKeys([
                    'total_files',
                    'total_additions',
                    'total_deletions',
                    'total_changes',
                    'large_changes',
                    'new_files',
                    'deleted_files',
                    'modified_files',
                    'renamed_files',
                ]);
        });

        it('categorizes files correctly', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    [
                        'sha' => 'abc123',
                        'filename' => 'tests/Feature/ExampleTest.php',
                        'status' => 'added',
                        'additions' => 20,
                        'deletions' => 0,
                        'changes' => 20,
                        'blob_url' => 'https://github.com/test/repo/blob/abc123/tests/Feature/ExampleTest.php',
                        'raw_url' => 'https://github.com/test/repo/raw/abc123/tests/Feature/ExampleTest.php',
                        'contents_url' => 'https://api.github.com/repos/test/repo/contents/tests/Feature/ExampleTest.php?ref=abc123',
                    ],
                    [
                        'sha' => 'def456',
                        'filename' => 'config/app.php',
                        'status' => 'modified',
                        'additions' => 5,
                        'deletions' => 2,
                        'changes' => 7,
                        'blob_url' => 'https://github.com/test/repo/blob/def456/config/app.php',
                        'raw_url' => 'https://github.com/test/repo/raw/def456/config/app.php',
                        'contents_url' => 'https://api.github.com/repos/test/repo/contents/config/app.php?ref=def456',
                    ],
                    [
                        'sha' => 'ghi789',
                        'filename' => 'README.md',
                        'status' => 'modified',
                        'additions' => 10,
                        'deletions' => 5,
                        'changes' => 15,
                        'blob_url' => 'https://github.com/test/repo/blob/ghi789/README.md',
                        'raw_url' => 'https://github.com/test/repo/raw/ghi789/README.md',
                        'contents_url' => 'https://api.github.com/repos/test/repo/contents/README.md?ref=ghi789',
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('test', 'repo', 1);

            expect($analysis['categories'])->toHaveKeys(['tests', 'config', 'docs', 'code', 'other']);
        });
    });

    describe('merge method with different MergeMethods', function () {
        it('can merge with Merge method', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['merged' => true], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::pullRequests()->merge(
                'test',
                'repo',
                1,
                'Merge commit',
                null,
                MergeMethod::Merge,
            );

            expect($result)->toBeTrue();
        });

        it('can merge with Squash method', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['merged' => true], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::pullRequests()->merge(
                'test',
                'repo',
                1,
                'Squash commit',
                null,
                MergeMethod::Squash,
            );

            expect($result)->toBeTrue();
        });

        it('can merge with Rebase method', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['merged' => true], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::pullRequests()->merge(
                'test',
                'repo',
                1,
                null,
                null,
                MergeMethod::Rebase,
            );

            expect($result)->toBeTrue();
        });

        it('uses Merge as default method', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['merged' => true], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::pullRequests()->merge('test', 'repo', 1);

            expect($result)->toBeTrue();
        });
    });
});
