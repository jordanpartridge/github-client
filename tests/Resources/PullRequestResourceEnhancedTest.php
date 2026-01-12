<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\PullRequestResourceEnhanced;
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

    $this->mockPRListData = function (array $overrides = []) {
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
            'user' => $this->createMockUserData('testuser', 1),
            'merged_by' => null,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'closed_at' => null,
        ], $overrides);
    };
});

describe('PullRequestResourceEnhanced', function () {
    it('extends PullRequestResource', function () {
        $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));

        expect($resource)->toBeInstanceOf(PullRequestResourceEnhanced::class);
    });

    describe('allWithCommentCounts method', function () {
        it('fetches PRs with complete data including comment counts', function () {
            // First response: list of PRs
            $listResponse = MockResponse::make([
                ($this->mockPRListData)(['number' => 1]),
                ($this->mockPRListData)(['number' => 2]),
            ], 200);

            // Subsequent responses: detailed PR data
            $detail1 = MockResponse::make(($this->mockPRData)(['number' => 1, 'comments' => 5]), 200);
            $detail2 = MockResponse::make(($this->mockPRData)(['number' => 2, 'comments' => 10]), 200);

            $mockClient = new MockClient();
            $mockClient->addResponse($listResponse);
            $mockClient->addResponse($detail1);
            $mockClient->addResponse($detail2);

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->allWithCommentCounts('test', 'repo');

            expect($prs)
                ->toBeArray()
                ->toHaveCount(2)
                ->and($prs[0])->toBeInstanceOf(PullRequestDTO::class);
        });

        it('limits to maxPRs parameter', function () {
            // Create list of 5 PRs
            $prList = [];
            for ($i = 1; $i <= 5; $i++) {
                $prList[] = ($this->mockPRListData)(['number' => $i]);
            }

            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make($prList, 200));

            // Only 3 detailed requests should be made
            for ($i = 1; $i <= 3; $i++) {
                $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => $i]), 200));
            }

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->allWithCommentCounts('test', 'repo', [], 3);

            expect($prs)->toHaveCount(3);
        });

        it('accepts query parameters', function () {
            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make([($this->mockPRListData)()], 200));
            $mockClient->addResponse(MockResponse::make(($this->mockPRData)(), 200));

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->allWithCommentCounts('test', 'repo', [
                'state' => 'open',
                'sort' => 'updated',
            ]);

            expect($prs)->toBeArray();
        });
    });

    describe('getMultipleWithCommentCounts method', function () {
        it('fetches specific PRs by number', function () {
            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => 5]), 200));
            $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => 10]), 200));
            $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => 15]), 200));

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->getMultipleWithCommentCounts('test', 'repo', [5, 10, 15]);

            expect($prs)
                ->toBeArray()
                ->toHaveCount(3);
        });

        it('skips PRs that cannot be fetched', function () {
            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => 1]), 200));
            $mockClient->addResponse(MockResponse::make(['message' => 'Not Found'], 404));
            $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => 3]), 200));

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->getMultipleWithCommentCounts('test', 'repo', [1, 2, 3]);

            // Should only have 2 PRs since #2 failed
            expect($prs)->toHaveCount(2);
        });

        it('handles empty PR list', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->getMultipleWithCommentCounts('test', 'repo', []);

            expect($prs)
                ->toBeArray()
                ->toBeEmpty();
        });
    });

    describe('recentWithCommentCounts method', function () {
        it('fetches recent PRs with default limit of 5', function () {
            $prList = [];
            for ($i = 1; $i <= 10; $i++) {
                $prList[] = ($this->mockPRListData)(['number' => $i]);
            }

            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make($prList, 200));
            for ($i = 1; $i <= 5; $i++) {
                $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => $i]), 200));
            }

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->recentWithCommentCounts('test', 'repo');

            expect($prs)->toHaveCount(5);
        });

        it('respects custom limit parameter', function () {
            $prList = [];
            for ($i = 1; $i <= 10; $i++) {
                $prList[] = ($this->mockPRListData)(['number' => $i]);
            }

            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make($prList, 200));
            for ($i = 1; $i <= 3; $i++) {
                $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => $i]), 200));
            }

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->recentWithCommentCounts('test', 'repo', 3);

            expect($prs)->toHaveCount(3);
        });

        it('caps limit at 20', function () {
            $prList = [];
            for ($i = 1; $i <= 30; $i++) {
                $prList[] = ($this->mockPRListData)(['number' => $i]);
            }

            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make($prList, 200));
            for ($i = 1; $i <= 20; $i++) {
                $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['number' => $i]), 200));
            }

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->recentWithCommentCounts('test', 'repo', 50);

            expect(count($prs))->toBeLessThanOrEqual(20);
        });

        it('filters by state', function () {
            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make([($this->mockPRListData)(['state' => 'closed'])], 200));
            $mockClient->addResponse(MockResponse::make(($this->mockPRData)(['state' => 'closed']), 200));

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->recentWithCommentCounts('test', 'repo', 5, 'closed');

            expect($prs)->toBeArray();
        });

        it('sorts by updated date descending', function () {
            $mockClient = new MockClient();
            $mockClient->addResponse(MockResponse::make([($this->mockPRListData)()], 200));
            $mockClient->addResponse(MockResponse::make(($this->mockPRData)(), 200));

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->recentWithCommentCounts('test', 'repo');

            expect($prs)->toBeArray();
        });
    });

    describe('inherits from PullRequestResource', function () {
        it('can use parent all method', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([($this->mockPRListData)()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $prs = $resource->all('test', 'repo');

            expect($prs)->toBeArray();
        });

        it('can use parent get method', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(($this->mockPRData)(), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $resource = new PullRequestResourceEnhanced(app(\JordanPartridge\GithubClient\Github::class));
            $pr = $resource->get('test', 'repo', 1);

            expect($pr)->toBeInstanceOf(PullRequestDTO::class);
        });
    });
});
