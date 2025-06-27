<?php

use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Issue;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;

describe('DTO conversion methods', function () {
    it('can create GitUserData from array and convert back', function () {
        $userData = [
            'login' => 'testuser',
            'id' => 123,
            'node_id' => 'MDQ6VXNlcjEyMw==',
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123?v=4',
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
        ];

        $dto = GitUserData::fromArray($userData);
        
        expect($dto)
            ->toBeInstanceOf(GitUserData::class)
            ->and($dto->login)->toBe('testuser')
            ->and($dto->id)->toBe(123)
            ->and($dto->site_admin)->toBeFalse();

        $converted = $dto->toArray();
        expect($converted)->toBe($userData);
    });

    it('can create Issue from API response and convert to array', function () {
        $mockUser = $this->createMockUserData('author', 456);
        
        $issueData = [
            'id' => 789,
            'node_id' => 'MDU6SXNzdWU3ODk=',
            'url' => 'https://api.github.com/repos/test/repo/issues/1',
            'repository_url' => 'https://api.github.com/repos/test/repo',
            'labels_url' => 'https://api.github.com/repos/test/repo/issues/1/labels{/name}',
            'comments_url' => 'https://api.github.com/repos/test/repo/issues/1/comments',
            'events_url' => 'https://api.github.com/repos/test/repo/issues/1/events',
            'html_url' => 'https://github.com/test/repo/issues/1',
            'number' => 1,
            'state' => 'open',
            'title' => 'Test Issue',
            'body' => 'This is a test issue',
            'user' => $mockUser,
            'labels' => [],
            'assignee' => null,
            'assignees' => [],
            'milestone' => null,
            'comments' => 0,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T01:00:00Z',
            'closed_at' => null,
            'closed_by' => null,
            'author_association' => 'OWNER',
            'active_lock_reason' => null,
            'locked' => false,
        ];

        $issue = Issue::fromApiResponse($issueData);
        
        expect($issue)
            ->toBeInstanceOf(Issue::class)
            ->and($issue->id)->toBe(789)
            ->and($issue->title)->toBe('Test Issue')
            ->and($issue->user)->toBeInstanceOf(GitUserData::class)
            ->and($issue->user->login)->toBe('author');

        $converted = $issue->toArray();
        expect($converted)
            ->toHaveKey('id', 789)
            ->toHaveKey('title', 'Test Issue')
            ->toHaveKey('user')
            ->and($converted['user'])->toBeArray()
            ->and($converted['user']['login'])->toBe('author');
    });

    it('can create PullRequestDTO from API response', function () {
        $mockUser = $this->createMockUserData('contributor', 999);
        
        $prData = [
            'id' => 555,
            'number' => 42,
            'state' => 'open',
            'title' => 'Add new feature',
            'body' => 'This PR adds a new feature',
            'html_url' => 'https://github.com/test/repo/pull/42',
            'diff_url' => 'https://github.com/test/repo/pull/42.diff',
            'patch_url' => 'https://github.com/test/repo/pull/42.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature-branch'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'comments' => 2,
            'review_comments' => 1,
            'commits' => 3,
            'additions' => 50,
            'deletions' => 10,
            'changed_files' => 4,
            'user' => $mockUser,
            'merged_by' => null,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T02:00:00Z',
            'closed_at' => null,
        ];

        $pr = PullRequestDTO::fromApiResponse($prData);
        
        expect($pr)
            ->toBeInstanceOf(PullRequestDTO::class)
            ->and($pr->id)->toBe(555)
            ->and($pr->number)->toBe(42)
            ->and($pr->base_ref)->toBe('main')
            ->and($pr->head_ref)->toBe('feature-branch')
            ->and($pr->user)->toBeInstanceOf(GitUserData::class)
            ->and($pr->user->login)->toBe('contributor');

        $converted = $pr->toArray();
        expect($converted)
            ->toHaveKey('id', 555)
            ->toHaveKey('number', 42)
            ->toHaveKey('base_ref', 'main')
            ->toHaveKey('head_ref', 'feature-branch')
            ->and($converted['user'])->toBeArray();
    });

    it('handles null values correctly in DTOs', function () {
        $userData = [
            'login' => 'testuser',
            'id' => 123,
            'node_id' => 'MDQ6VXNlcjEyMw==',
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123?v=4',
            'gravatar_id' => null, // Testing null handling
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
            'site_admin' => false,
        ];

        $dto = GitUserData::fromArray($userData);
        
        expect($dto->gravatar_id)->toBe('');
        expect($dto->user_view_type)->toBe('');
        
        $converted = $dto->toArray();
        expect($converted['gravatar_id'])->toBe('');
        expect($converted['user_view_type'])->toBe('');
    });

    it('preserves nested object relationships', function () {
        $mockUser = $this->createMockUserData('author', 111);
        $mockAssignee = $this->createMockUserData('assignee', 222);
        
        $issueData = [
            'id' => 333,
            'node_id' => 'MDU6SXNzdWUzMzM=',
            'url' => 'https://api.github.com/repos/test/repo/issues/1',
            'repository_url' => 'https://api.github.com/repos/test/repo',
            'labels_url' => 'https://api.github.com/repos/test/repo/issues/1/labels{/name}',
            'comments_url' => 'https://api.github.com/repos/test/repo/issues/1/comments',
            'events_url' => 'https://api.github.com/repos/test/repo/issues/1/events',
            'html_url' => 'https://github.com/test/repo/issues/1',
            'number' => 1,
            'state' => 'open',
            'title' => 'Test Issue with Assignee',
            'body' => 'This issue has an assignee',
            'user' => $mockUser,
            'labels' => [],
            'assignee' => $mockAssignee,
            'assignees' => [$mockAssignee],
            'milestone' => null,
            'comments' => 0,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T01:00:00Z',
            'closed_at' => null,
            'closed_by' => null,
            'author_association' => 'OWNER',
            'active_lock_reason' => null,
            'locked' => false,
        ];

        $issue = Issue::fromApiResponse($issueData);
        
        expect($issue->assignee)
            ->toBeInstanceOf(GitUserData::class)
            ->and($issue->assignee->login)->toBe('assignee')
            ->and($issue->assignees)->toHaveCount(1)
            ->and($issue->assignees[0])->toBeInstanceOf(GitUserData::class)
            ->and($issue->assignees[0]->login)->toBe('assignee');

        $converted = $issue->toArray();
        expect($converted['assignee'])
            ->toBeArray()
            ->and($converted['assignee']['login'])->toBe('assignee')
            ->and($converted['assignees'])->toHaveCount(1)
            ->and($converted['assignees'][0])->toBeArray()
            ->and($converted['assignees'][0]['login'])->toBe('assignee');
    });
});