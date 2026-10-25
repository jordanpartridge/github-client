<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestReviewDTO;
use JordanPartridge\GithubClient\Enums\MergeMethod;
use JordanPartridge\GithubClient\Facades\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Illuminate\Support\Collection;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);

    // Set up default mock response for a pull request
    $mockClient = new MockClient([
        '*' => MockResponse::make([
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
            'comments' => 0,
            'review_comments' => 0,
            'commits' => 1,
            'additions' => 10,
            'deletions' => 5,
            'changed_files' => 2,
            'user' => $this->createMockUserData('testuser', 1),
            'merged_by' => null,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'closed_at' => null,
        ], 200),
    ]);

    Github::connector()->withMockClient($mockClient);
});

describe('pull request operations', function () {
    it('can list pull requests', function () {
        // Override mock for list operation (expects array of PRs)
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([[
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
                'comments' => 0,
                'review_comments' => 0,
                'commits' => 1,
                'additions' => 10,
                'deletions' => 5,
                'changed_files' => 2,
                'user' => $this->createMockUserData('testuser', 1),
                'merged_by' => null,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'closed_at' => null,
            ]], 200),
        ]));

        $response = Github::pullRequests()->all('test', 'repo');

        expect($response)
            ->toBeArray()
            ->and($response[0])
            ->toBeInstanceOf(PullRequestDTO::class)
            ->and($response[0]->title)->toBe('Test Pull Request');
    });

    it('can get a specific pull request', function () {
        $pullRequest = Github::pullRequests()->get('test', 'repo', 1);

        expect($pullRequest)
            ->toBeInstanceOf(PullRequestDTO::class)
            ->and($pullRequest->number)->toBe(1)
            ->and($pullRequest->state)->toBe('open');
    });

    it('can create a pull request', function () {
        $pullRequest = Github::pullRequests()->create(
            'test',
            'repo',
            'New Feature',
            'feature-branch',
            'main',
            'Adding a new feature',
            false,
        );

        expect($pullRequest)
            ->toBeInstanceOf(PullRequestDTO::class)
            ->and($pullRequest->title)->toBe('Test Pull Request');
    });

    it('can update a pull request', function () {
        $pullRequest = Github::pullRequests()->update('test', 'repo', 1, [
            'title' => 'Updated Title',
        ]);

        expect($pullRequest)
            ->toBeInstanceOf(PullRequestDTO::class);
    });

    it('can merge a pull request', function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make(['merged' => true], 200),
        ]));

        $merged = Github::pullRequests()->merge(
            'test',
            'repo',
            1,
            'Merging feature',
            null,
            MergeMethod::Squash,
        );

        expect($merged)->toBeTrue();
    });
});

describe('pull request reviews', function () {
    beforeEach(function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([
                [
                    'id' => 1,
                    'node_id' => 'abc123',
                    'user' => $this->createMockUserData('reviewer', 2),
                    'body' => 'Looks good!',
                    'state' => 'APPROVED',
                    'html_url' => 'https://github.com/test/repo/pull/1#pullrequestreview-1',
                    'pull_request_url' => 'https://api.github.com/repos/test/repo/pulls/1',
                    'commit_id' => 'abc123def456',
                    'submitted_at' => '2024-01-01T00:00:00Z',
                ],
            ], 200),
        ]));
    });

    it('can list pull request reviews', function () {
        $reviews = Github::pullRequests()->reviews('test', 'repo', 1);

        expect($reviews)
            ->toBeInstanceOf(Collection::class)
            ->and($reviews->isEmpty())->toBeFalse()
            ->and($reviews->first())
            ->toBeInstanceOf(PullRequestReviewDTO::class)
            ->and($reviews->first()->state)->toBe('APPROVED');
    });

    it('can create a pull request review', function () {
        // Override mock for create operation (expects single review object)
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([
                'id' => 1,
                'node_id' => 'abc123',
                'user' => $this->createMockUserData('reviewer', 2),
                'body' => 'Looks good!',
                'state' => 'APPROVED',
                'html_url' => 'https://github.com/test/repo/pull/1#pullrequestreview-1',
                'pull_request_url' => 'https://api.github.com/repos/test/repo/pulls/1',
                'commit_id' => 'abc123def456',
                'submitted_at' => '2024-01-01T00:00:00Z',
            ], 200),
        ]));

        $review = Github::pullRequests()->createReview(
            'test',
            'repo',
            1,
            'Great work!',
            'APPROVE',
        );

        expect($review)
            ->toBeInstanceOf(PullRequestReviewDTO::class)
            ->and($review->state)->toBe('APPROVED');
    });
});

describe('pull request comments', function () {
    beforeEach(function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([
                [
                    'id' => 1,
                    'node_id' => 'abc123',
                    'path' => 'src/test.php',
                    'position' => 5,
                    'original_position' => 5,
                    'commit_id' => 'abc123def456',
                    'original_commit_id' => 'abc123def456',
                    'user' => $this->createMockUserData('commenter', 3),
                    'body' => 'Consider using a different approach here',
                    'html_url' => 'https://github.com/test/repo/pull/1#discussion_r1',
                    'pull_request_url' => 'https://api.github.com/repos/test/repo/pulls/1',
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-01T00:00:00Z',
                ],
            ], 200),
        ]));
    });

    it('can list pull request comments', function () {
        $comments = Github::pullRequests()->comments('test', 'repo', 1);

        expect($comments)
            ->toBeArray()
            ->and($comments[0])
            ->toBeInstanceOf(PullRequestCommentDTO::class)
            ->and($comments[0]->body)->toBe('Consider using a different approach here');
    });

    it('can create a pull request comment', function () {
        // Override mock for create operation (expects single comment object)
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([
                'id' => 1,
                'node_id' => 'abc123',
                'path' => 'src/test.php',
                'position' => 5,
                'original_position' => 5,
                'commit_id' => 'abc123def456',
                'original_commit_id' => 'abc123def456',
                'user' => $this->createMockUserData('commenter', 3),
                'body' => 'Consider using a different approach here',
                'html_url' => 'https://github.com/test/repo/pull/1#discussion_r1',
                'pull_request_url' => 'https://api.github.com/repos/test/repo/pulls/1',
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
            ], 200),
        ]));

        $comment = Github::pullRequests()->createComment(
            'test',
            'repo',
            1,
            'This could be improved',
            'abc123def456',
            'src/test.php',
            5,
        );

        expect($comment)
            ->toBeInstanceOf(PullRequestCommentDTO::class)
            ->and($comment->body)->toBe('Consider using a different approach here');
    });
});

describe('manual pagination', function () {
    beforeEach(function () {
        config(['github-client.token' => 'fake-token']);
    });

    it('fetches all pull requests across multiple pages', function () {
        $page1Response = MockResponse::make([
            [
                'id' => 1,
                'number' => 1,
                'state' => 'open',
                'title' => 'PR 1',
                'body' => '',
                'html_url' => 'https://github.com/test/repo/pull/1',
                'diff_url' => 'https://github.com/test/repo/pull/1.diff',
                'patch_url' => 'https://github.com/test/repo/pull/1.patch',
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature-1'],
                'draft' => false,
                'merged' => false,
                'merged_at' => null,
                'merge_commit_sha' => null,
                'comments' => 0,
                'review_comments' => 0,
                'commits' => 1,
                'additions' => 10,
                'deletions' => 5,
                'changed_files' => 2,
                'user' => $this->createMockUserData('testuser', 1),
                'merged_by' => null,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'closed_at' => null,
            ],
            [
                'id' => 2,
                'number' => 2,
                'state' => 'open',
                'title' => 'PR 2',
                'body' => '',
                'html_url' => 'https://github.com/test/repo/pull/2',
                'diff_url' => 'https://github.com/test/repo/pull/2.diff',
                'patch_url' => 'https://github.com/test/repo/pull/2.patch',
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature-2'],
                'draft' => false,
                'merged' => false,
                'merged_at' => null,
                'merge_commit_sha' => null,
                'comments' => 0,
                'review_comments' => 0,
                'commits' => 1,
                'additions' => 10,
                'deletions' => 5,
                'changed_files' => 2,
                'user' => $this->createMockUserData('testuser', 1),
                'merged_by' => null,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'closed_at' => null,
            ],
        ], 200, [
            'Link' => '</repos/test/repo/pulls?page=2>; rel="next", </repos/test/repo/pulls?page=2>; rel="last"',
        ]);

        $page2Response = MockResponse::make([
            [
                'id' => 3,
                'number' => 3,
                'state' => 'open',
                'title' => 'PR 3',
                'body' => '',
                'html_url' => 'https://github.com/test/repo/pull/3',
                'diff_url' => 'https://github.com/test/repo/pull/3.diff',
                'patch_url' => 'https://github.com/test/repo/pull/3.patch',
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature-3'],
                'draft' => false,
                'merged' => false,
                'merged_at' => null,
                'merge_commit_sha' => null,
                'comments' => 0,
                'review_comments' => 0,
                'commits' => 1,
                'additions' => 10,
                'deletions' => 5,
                'changed_files' => 2,
                'user' => $this->createMockUserData('testuser', 1),
                'merged_by' => null,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'closed_at' => null,
            ],
        ], 200, [
            'Link' => '</repos/test/repo/pulls?page=1>; rel="first", </repos/test/repo/pulls?page=1>; rel="prev"',
        ]);

        $mockClient = new MockClient();
        $mockClient->addResponse($page1Response);
        $mockClient->addResponse($page2Response);

        Github::connector()->withMockClient($mockClient);

        $allPrs = Github::pullRequests()->paginate('test', 'repo');

        expect($allPrs)
            ->toBeArray()
            ->toHaveCount(3)
            ->and($allPrs[0]->title)->toBe('PR 1')
            ->and($allPrs[1]->title)->toBe('PR 2')
            ->and($allPrs[2]->title)->toBe('PR 3');
    });

    it('stops pagination when no more pages available', function () {
        $singlePageResponse = MockResponse::make([
            [
                'id' => 1,
                'number' => 1,
                'state' => 'open',
                'title' => 'PR 1',
                'body' => '',
                'html_url' => 'https://github.com/test/repo/pull/1',
                'diff_url' => 'https://github.com/test/repo/pull/1.diff',
                'patch_url' => 'https://github.com/test/repo/pull/1.patch',
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature-1'],
                'draft' => false,
                'merged' => false,
                'merged_at' => null,
                'merge_commit_sha' => null,
                'comments' => 0,
                'review_comments' => 0,
                'commits' => 1,
                'additions' => 10,
                'deletions' => 5,
                'changed_files' => 2,
                'user' => $this->createMockUserData('testuser', 1),
                'merged_by' => null,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'closed_at' => null,
            ],
        ], 200);

        Github::connector()->withMockClient(new MockClient([
            '*' => $singlePageResponse,
        ]));

        $allPrs = Github::pullRequests()->paginate('test', 'repo');

        expect($allPrs)
            ->toBeArray()
            ->toHaveCount(1)
            ->and($allPrs[0]->title)->toBe('PR 1');
    });

    it('handles empty pull request list', function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([], 200),
        ]));

        $allPrs = Github::pullRequests()->paginate('test', 'repo');

        expect($allPrs)
            ->toBeArray()
            ->toHaveCount(0);
    });

    it('respects filtering parameters during pagination', function () {
        $openPrsResponse = MockResponse::make([
            [
                'id' => 1,
                'number' => 1,
                'state' => 'open',
                'title' => 'Open PR',
                'body' => '',
                'html_url' => 'https://github.com/test/repo/pull/1',
                'diff_url' => 'https://github.com/test/repo/pull/1.diff',
                'patch_url' => 'https://github.com/test/repo/pull/1.patch',
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature-1'],
                'draft' => false,
                'merged' => false,
                'merged_at' => null,
                'merge_commit_sha' => null,
                'comments' => 0,
                'review_comments' => 0,
                'commits' => 1,
                'additions' => 10,
                'deletions' => 5,
                'changed_files' => 2,
                'user' => $this->createMockUserData('testuser', 1),
                'merged_by' => null,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'closed_at' => null,
            ],
        ], 200);

        Github::connector()->withMockClient(new MockClient([
            '*' => $openPrsResponse,
        ]));

        $allPrs = Github::pullRequests()->paginate('test', 'repo', [
            'state' => 'open',
            'sort' => 'created',
            'direction' => 'desc',
        ]);

        expect($allPrs)
            ->toBeArray()
            ->toHaveCount(1)
            ->and($allPrs[0]->title)->toBe('Open PR');
    });

    it('throws exception when maximum page limit is exceeded', function () {
        $infiniteResponse = MockResponse::make([
            [
                'id' => 1,
                'number' => 1,
                'state' => 'open',
                'title' => 'PR 1',
                'body' => '',
                'html_url' => 'https://github.com/test/repo/pull/1',
                'diff_url' => 'https://github.com/test/repo/pull/1.diff',
                'patch_url' => 'https://github.com/test/repo/pull/1.patch',
                'base' => ['ref' => 'main'],
                'head' => ['ref' => 'feature-1'],
                'draft' => false,
                'merged' => false,
                'merged_at' => null,
                'merge_commit_sha' => null,
                'comments' => 0,
                'review_comments' => 0,
                'commits' => 1,
                'additions' => 10,
                'deletions' => 5,
                'changed_files' => 2,
                'user' => $this->createMockUserData('testuser', 1),
                'merged_by' => null,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'closed_at' => null,
            ],
        ], 200, [
            'Link' => '</repos/test/repo/pulls?page=2>; rel="next"',
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => $infiniteResponse,
        ]));

        expect(fn () => Github::pullRequests()->paginate('test', 'repo'))
            ->toThrow(RuntimeException::class, 'Maximum page limit (1000) exceeded during pagination');
    });
});
