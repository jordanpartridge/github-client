<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDetailDTO;

describe('Pull Request Improvements', function () {

    it('can handle merge status fields in detail DTO', function () {
        $mockData = [
            'id' => 123,
            'number' => 42,
            'state' => 'open',
            'title' => 'Test PR',
            'body' => 'Test body',
            'html_url' => 'https://github.com/test/repo/pull/42',
            'diff_url' => 'https://github.com/test/repo/pull/42.diff',
            'patch_url' => 'https://github.com/test/repo/pull/42.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature-branch'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'user' => [
                'login' => 'testuser',
                'id' => 456,
                'node_id' => 'MDQ6VXNlcjQ1Ng==',
                'avatar_url' => 'https://github.com/avatar.jpg',
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

            // Detail fields
            'comments' => 5,
            'review_comments' => 10,
            'commits' => 3,
            'additions' => 50,
            'deletions' => 20,
            'changed_files' => 4,

            // New merge status fields
            'mergeable' => false,
            'mergeable_state' => 'dirty',
            'rebaseable' => false,
        ];

        $dto = PullRequestDetailDTO::fromDetailResponse($mockData);

        expect($dto->mergeable)->toBe(false)
            ->and($dto->mergeable_state)->toBe('dirty')
            ->and($dto->rebaseable)->toBe(false)
            ->and($dto->hasMergeConflicts())->toBeTrue()
            ->and($dto->isReadyToMerge())->toBeFalse()
            ->and($dto->canRebase())->toBeFalse()
            ->and($dto->getMergeStatusDescription())->toBe('Has merge conflicts');
    });

    it('can handle clean merge status', function () {
        $mockData = [
            'id' => 123,
            'number' => 42,
            'state' => 'open',
            'title' => 'Test PR',
            'body' => 'Test body',
            'html_url' => 'https://github.com/test/repo/pull/42',
            'diff_url' => 'https://github.com/test/repo/pull/42.diff',
            'patch_url' => 'https://github.com/test/repo/pull/42.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature-branch'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'user' => [
                'login' => 'testuser',
                'id' => 456,
                'node_id' => 'MDQ6VXNlcjQ1Ng==',
                'avatar_url' => 'https://github.com/avatar.jpg',
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

            // Detail fields
            'comments' => 0,
            'review_comments' => 0,
            'commits' => 1,
            'additions' => 10,
            'deletions' => 0,
            'changed_files' => 1,

            // Clean merge status
            'mergeable' => true,
            'mergeable_state' => 'clean',
            'rebaseable' => true,
        ];

        $dto = PullRequestDetailDTO::fromDetailResponse($mockData);

        expect($dto->mergeable)->toBe(true)
            ->and($dto->mergeable_state)->toBe('clean')
            ->and($dto->rebaseable)->toBe(true)
            ->and($dto->hasMergeConflicts())->toBeFalse()
            ->and($dto->isReadyToMerge())->toBeTrue()
            ->and($dto->canRebase())->toBeTrue()
            ->and($dto->getMergeStatusDescription())->toBe('Ready to merge');
    });

    it('includes merge status in summary', function () {
        $mockData = [
            'id' => 123,
            'number' => 42,
            'state' => 'open',
            'title' => 'Feature Addition',
            'body' => 'Test body',
            'html_url' => 'https://github.com/test/repo/pull/42',
            'diff_url' => 'https://github.com/test/repo/pull/42.diff',
            'patch_url' => 'https://github.com/test/repo/pull/42.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature-branch'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'user' => [
                'login' => 'developer',
                'id' => 456,
                'node_id' => 'MDQ6VXNlcjQ1Ng==',
                'avatar_url' => 'https://github.com/avatar.jpg',
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
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'closed_at' => null,

            // Detail fields
            'comments' => 2,
            'review_comments' => 5,
            'commits' => 4,
            'additions' => 150,
            'deletions' => 30,
            'changed_files' => 8,

            // Merge status
            'mergeable' => true,
            'mergeable_state' => 'clean',
            'rebaseable' => true,
        ];

        $dto = PullRequestDetailDTO::fromDetailResponse($mockData);
        $summary = $dto->getSummary();

        expect($summary['merge_status'])->toHaveKey('mergeable', true)
            ->and($summary['merge_status'])->toHaveKey('mergeable_state', 'clean')
            ->and($summary['merge_status'])->toHaveKey('rebaseable', true)
            ->and($summary['merge_status'])->toHaveKey('description', 'Ready to merge')
            ->and($summary['pr'])->toBe('#42: Feature Addition')
            ->and($summary['author'])->toBe('developer');
    });

    it('handles null merge status gracefully', function () {
        $mockData = [
            'id' => 123,
            'number' => 42,
            'state' => 'open',
            'title' => 'Test PR',
            'body' => 'Test body',
            'html_url' => 'https://github.com/test/repo/pull/42',
            'diff_url' => 'https://github.com/test/repo/pull/42.diff',
            'patch_url' => 'https://github.com/test/repo/pull/42.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature-branch'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'user' => [
                'login' => 'testuser',
                'id' => 456,
                'node_id' => 'MDQ6VXNlcjQ1Ng==',
                'avatar_url' => 'https://github.com/avatar.jpg',
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

            // Detail fields
            'comments' => 0,
            'review_comments' => 0,
            'commits' => 1,
            'additions' => 5,
            'deletions' => 2,
            'changed_files' => 1,

            // Null merge status (GitHub still calculating)
            'mergeable' => null,
            'mergeable_state' => 'unknown',
            'rebaseable' => null,
        ];

        $dto = PullRequestDetailDTO::fromDetailResponse($mockData);

        expect($dto->mergeable)->toBeNull()
            ->and($dto->mergeable_state)->toBe('unknown')
            ->and($dto->rebaseable)->toBeNull()
            ->and($dto->getMergeStatusDescription())->toBe('Merge status unknown (checking...)');
    });

    it('includes merge fields in toArray output', function () {
        $mockData = [
            'id' => 123,
            'number' => 42,
            'state' => 'open',
            'title' => 'Test PR',
            'body' => 'Test body',
            'html_url' => 'https://github.com/test/repo/pull/42',
            'diff_url' => 'https://github.com/test/repo/pull/42.diff',
            'patch_url' => 'https://github.com/test/repo/pull/42.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature-branch'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'user' => [
                'login' => 'testuser',
                'id' => 456,
                'node_id' => 'MDQ6VXNlcjQ1Ng==',
                'avatar_url' => 'https://github.com/avatar.jpg',
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

            // Detail fields
            'comments' => 3,
            'review_comments' => 7,
            'commits' => 2,
            'additions' => 25,
            'deletions' => 10,
            'changed_files' => 3,

            // Merge status
            'mergeable' => false,
            'mergeable_state' => 'dirty',
            'rebaseable' => false,
        ];

        $dto = PullRequestDetailDTO::fromDetailResponse($mockData);
        $array = $dto->toArray();

        expect($array)->toHaveKey('mergeable', false)
            ->and($array)->toHaveKey('mergeable_state', 'dirty')
            ->and($array)->toHaveKey('rebaseable', false)
            ->and($array)->toHaveKey('comments', 3)
            ->and($array)->toHaveKey('review_comments', 7);
    });
});
