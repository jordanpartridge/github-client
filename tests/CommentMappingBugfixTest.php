<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;

describe('Comment Mapping Bug Fix (Issue #71)', function () {
    it('handles GitHub API returning string values for comment counts', function () {
        // This reproduces the exact scenario from conduit know:
        // API returns comments: 1, review_comments: 20 but DTO shows 0, 0
        $githubApiResponse = [
            'id' => 123456,
            'number' => 42,
            'state' => 'open',
            'title' => 'Fix comment mapping bug',
            'body' => 'This PR fixes the comment count mapping issue',
            'html_url' => 'https://github.com/test/repo/pull/42',
            'diff_url' => 'https://github.com/test/repo/pull/42.diff',
            'patch_url' => 'https://github.com/test/repo/pull/42.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'fix/comment-mapping'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,

            // The problem scenario: API might return strings instead of integers
            'comments' => '1',         // String value from API
            'review_comments' => '20', // String value from API
            'commits' => '3',
            'additions' => '150',
            'deletions' => '75',
            'changed_files' => '8',

            'user' => [
                'login' => 'developer',
                'id' => 12345,
                'node_id' => 'MDQ6VXNlcjEyMzQ1',
                'avatar_url' => 'https://github.com/developer.png',
                'gravatar_id' => '',
                'url' => 'https://api.github.com/users/developer',
                'html_url' => 'https://github.com/developer',
                'followers_url' => 'https://api.github.com/users/developer/followers',
                'following_url' => 'https://api.github.com/users/developer/following{/other_user}',
                'gists_url' => 'https://api.github.com/users/developer/gists{/gist_id}',
                'starred_url' => 'https://api.github.com/users/developer/starred{/owner}{/repo}',
                'subscriptions_url' => 'https://api.github.com/users/developer/subscriptions',
                'organizations_url' => 'https://api.github.com/users/developer/orgs',
                'repos_url' => 'https://api.github.com/users/developer/repos',
                'events_url' => 'https://api.github.com/users/developer/events{/privacy}',
                'received_events_url' => 'https://api.github.com/users/developer/received_events',
                'type' => 'User',
                'user_view_type' => 'public',
                'site_admin' => false,
            ],
            'merged_by' => null,
            'created_at' => '2024-12-20T10:00:00Z',
            'updated_at' => '2024-12-20T15:30:00Z',
            'closed_at' => null,
        ];

        // Create DTO from the API response
        $dto = PullRequestDTO::fromApiResponse($githubApiResponse);

        // Verify the bug is fixed: should correctly map string values to integers
        expect($dto->comments)->toBe(1)
            ->and($dto->review_comments)->toBe(20)
            ->and($dto->commits)->toBe(3)
            ->and($dto->additions)->toBe(150)
            ->and($dto->deletions)->toBe(75)
            ->and($dto->changed_files)->toBe(8);

        // Verify types are correct
        expect($dto->comments)->toBeInt()
            ->and($dto->review_comments)->toBeInt()
            ->and($dto->commits)->toBeInt()
            ->and($dto->additions)->toBeInt()
            ->and($dto->deletions)->toBeInt()
            ->and($dto->changed_files)->toBeInt();
    });

    it('handles null values gracefully with type coercion', function () {
        // Test edge case where some values might be null
        $apiResponseWithNulls = [
            'id' => 789,
            'number' => 100,
            'state' => 'closed',
            'title' => 'Null handling test',
            'body' => 'Testing null value handling',
            'html_url' => 'https://github.com/test/repo/pull/100',
            'diff_url' => 'https://github.com/test/repo/pull/100.diff',
            'patch_url' => 'https://github.com/test/repo/pull/100.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'test-nulls'],
            'draft' => false,
            'merged' => true,
            'merged_at' => '2024-12-15T12:00:00Z',
            'merge_commit_sha' => 'abc123def456',

            // Some fields might be null or missing
            'comments' => null,
            'review_comments' => null,
            // commits, additions, deletions, changed_files intentionally missing

            'user' => [
                'login' => 'tester',
                'id' => 67890,
                'node_id' => 'MDQ6VXNlcjY3ODkw',
                'avatar_url' => 'https://github.com/tester.png',
                'gravatar_id' => '',
                'url' => 'https://api.github.com/users/tester',
                'html_url' => 'https://github.com/tester',
                'followers_url' => 'https://api.github.com/users/tester/followers',
                'following_url' => 'https://api.github.com/users/tester/following{/other_user}',
                'gists_url' => 'https://api.github.com/users/tester/gists{/gist_id}',
                'starred_url' => 'https://api.github.com/users/tester/starred{/owner}{/repo}',
                'subscriptions_url' => 'https://api.github.com/users/tester/subscriptions',
                'organizations_url' => 'https://api.github.com/users/tester/orgs',
                'repos_url' => 'https://api.github.com/users/tester/repos',
                'events_url' => 'https://api.github.com/users/tester/events{/privacy}',
                'received_events_url' => 'https://api.github.com/users/tester/received_events',
                'type' => 'User',
                'user_view_type' => 'public',
                'site_admin' => false,
            ],
            'merged_by' => [
                'login' => 'merger',
                'id' => 111,
                'node_id' => 'MDQ6VXNlcjExMQ==',
                'avatar_url' => 'https://github.com/merger.png',
                'gravatar_id' => '',
                'url' => 'https://api.github.com/users/merger',
                'html_url' => 'https://github.com/merger',
                'followers_url' => 'https://api.github.com/users/merger/followers',
                'following_url' => 'https://api.github.com/users/merger/following{/other_user}',
                'gists_url' => 'https://api.github.com/users/merger/gists{/gist_id}',
                'starred_url' => 'https://api.github.com/users/merger/starred{/owner}{/repo}',
                'subscriptions_url' => 'https://api.github.com/users/merger/subscriptions',
                'organizations_url' => 'https://api.github.com/users/merger/orgs',
                'repos_url' => 'https://api.github.com/users/merger/repos',
                'events_url' => 'https://api.github.com/users/merger/events{/privacy}',
                'received_events_url' => 'https://api.github.com/users/merger/received_events',
                'type' => 'User',
                'user_view_type' => 'public',
                'site_admin' => false,
            ],
            'created_at' => '2024-12-10T09:00:00Z',
            'updated_at' => '2024-12-15T12:00:00Z',
            'closed_at' => '2024-12-15T12:00:00Z',
        ];

        $dto = PullRequestDTO::fromApiResponse($apiResponseWithNulls);

        // Should default to 0 for null values and missing fields
        expect($dto->comments)->toBe(0)
            ->and($dto->review_comments)->toBe(0)
            ->and($dto->commits)->toBe(0)
            ->and($dto->additions)->toBe(0)
            ->and($dto->deletions)->toBe(0)
            ->and($dto->changed_files)->toBe(0);

        // Verify other fields are correct
        expect($dto->merged)->toBe(true)
            ->and($dto->merged_by)->not->toBeNull()
            ->and($dto->merged_by->login)->toBe('merger');
    });
});
