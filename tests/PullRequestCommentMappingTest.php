<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;

describe('PullRequest Comment Mapping Diagnostics', function () {
    it('correctly maps comments from GitHub API response', function () {
        // Test with real GitHub API structure
        $mockApiResponse = [
            'id' => 1,
            'number' => 1,
            'state' => 'open',
            'title' => 'Test PR with comments',
            'body' => 'PR description',
            'html_url' => 'https://github.com/test/repo/pull/1',
            'diff_url' => 'https://github.com/test/repo/pull/1.diff',
            'patch_url' => 'https://github.com/test/repo/pull/1.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'comments' => 5,           // Issue-style comments
            'review_comments' => 12,   // Inline review comments
            'commits' => 3,
            'additions' => 100,
            'deletions' => 50,
            'changed_files' => 4,
            'user' => [
                'login' => 'testuser',
                'id' => 1,
                'node_id' => 'MDQ6VXNlcjE=',
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
                'user_view_type' => 'public',
                'site_admin' => false,
            ],
            'merged_by' => null,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'closed_at' => null,
        ];

        $dto = PullRequestDTO::fromApiResponse($mockApiResponse);

        // Verify comment mapping is working correctly
        expect($dto->comments)->toBe(5)
            ->and($dto->review_comments)->toBe(12)
            ->and($dto->title)->toBe('Test PR with comments');
    });

    it('handles edge case where comments are strings', function () {
        // Some GitHub API responses might return string values
        $mockApiResponse = [
            'id' => 1,
            'number' => 1,
            'state' => 'open',
            'title' => 'Test PR',
            'body' => 'PR description',
            'html_url' => 'https://github.com/test/repo/pull/1',
            'diff_url' => 'https://github.com/test/repo/pull/1.diff',
            'patch_url' => 'https://github.com/test/repo/pull/1.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature'],
            'draft' => false,
            'merged' => false,
            'comments' => '7',          // String instead of int
            'review_comments' => '15',  // String instead of int
            'commits' => '2',
            'additions' => '200',
            'deletions' => '100',
            'changed_files' => '3',
            'user' => [
                'login' => 'testuser',
                'id' => 1,
                'node_id' => 'MDQ6VXNlcjE=',
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
                'user_view_type' => 'public',
                'site_admin' => false,
            ],
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
        ];

        // This might fail if our DTO expects strict integers
        $dto = PullRequestDTO::fromApiResponse($mockApiResponse);

        expect($dto->comments)->toBe(7)
            ->and($dto->review_comments)->toBe(15);
    });

    it('debug: logs actual API response structure', function () {
        // This test helps understand what the real API response looks like
        $debugApiResponse = [
            'id' => 999,
            'number' => 42,
            'state' => 'open',
            'title' => 'Debug PR',
            'body' => 'This PR is for debugging comment mapping',
            'html_url' => 'https://github.com/debug/repo/pull/42',
            'diff_url' => 'https://github.com/debug/repo/pull/42.diff',
            'patch_url' => 'https://github.com/debug/repo/pull/42.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'debug-feature'],
            'draft' => false,
            'merged' => false,
            // Test different comment scenarios
            'comments' => 1,
            'review_comments' => 20,
            'commits' => 5,
            'additions' => 500,
            'deletions' => 200,
            'changed_files' => 10,
            'user' => [
                'login' => 'debuguser',
                'id' => 999,
                'node_id' => 'MDQ6VXNlcjk5OQ==',
                'avatar_url' => 'https://github.com/debuguser.png',
                'gravatar_id' => '',
                'url' => 'https://api.github.com/users/debuguser',
                'html_url' => 'https://github.com/debuguser',
                'followers_url' => 'https://api.github.com/users/debuguser/followers',
                'following_url' => 'https://api.github.com/users/debuguser/following{/other_user}',
                'gists_url' => 'https://api.github.com/users/debuguser/gists{/gist_id}',
                'starred_url' => 'https://api.github.com/users/debuguser/starred{/owner}{/repo}',
                'subscriptions_url' => 'https://api.github.com/users/debuguser/subscriptions',
                'organizations_url' => 'https://api.github.com/users/debuguser/orgs',
                'repos_url' => 'https://api.github.com/users/debuguser/repos',
                'events_url' => 'https://api.github.com/users/debuguser/events{/privacy}',
                'received_events_url' => 'https://api.github.com/users/debuguser/received_events',
                'type' => 'User',
                'user_view_type' => 'public',
                'site_admin' => false,
            ],
            'created_at' => '2024-12-01T10:30:00Z',
            'updated_at' => '2024-12-01T15:45:00Z',
        ];

        $dto = PullRequestDTO::fromApiResponse($debugApiResponse);

        // The exact scenario mentioned in conduit know
        expect($dto->comments)->toBe(1)
            ->and($dto->review_comments)->toBe(20)
            ->and($dto->number)->toBe(42);

        // Debug output to understand the mapping
        expect([
            'original_comments' => $debugApiResponse['comments'],
            'original_review_comments' => $debugApiResponse['review_comments'],
            'dto_comments' => $dto->comments,
            'dto_review_comments' => $dto->review_comments,
            'mapping_working' => ($dto->comments === $debugApiResponse['comments'] &&
                                 $dto->review_comments === $debugApiResponse['review_comments']),
        ])->toEqual([
            'original_comments' => 1,
            'original_review_comments' => 20,
            'dto_comments' => 1,
            'dto_review_comments' => 20,
            'mapping_working' => true,
        ]);
    });
});
