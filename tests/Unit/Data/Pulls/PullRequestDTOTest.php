<?php

use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;

beforeEach(function () {
    $this->sampleData = [
        'id' => 123456,
        'number' => 42,
        'state' => 'open',
        'title' => 'Add new feature',
        'body' => 'This PR adds a new feature for user authentication.',
        'html_url' => 'https://github.com/owner/repo/pull/42',
        'diff_url' => 'https://github.com/owner/repo/pull/42.diff',
        'patch_url' => 'https://github.com/owner/repo/pull/42.patch',
        'base' => ['ref' => 'main'],
        'head' => ['ref' => 'feature-auth'],
        'draft' => false,
        'merged' => false,
        'merged_at' => null,
        'merge_commit_sha' => null,
        'comments' => 5,
        'review_comments' => 10,
        'commits' => 3,
        'additions' => 150,
        'deletions' => 50,
        'changed_files' => 8,
        'user' => $this->createMockUserData('developer', 1),
        'merged_by' => null,
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-16T14:30:00Z',
        'closed_at' => null,
    ];
});

it('can create PullRequestDTO from API response', function () {
    $pr = PullRequestDTO::fromApiResponse($this->sampleData);

    expect($pr->id)->toBe(123456);
    expect($pr->number)->toBe(42);
    expect($pr->state)->toBe('open');
    expect($pr->title)->toBe('Add new feature');
    expect($pr->body)->toBe('This PR adds a new feature for user authentication.');
    expect($pr->html_url)->toBe('https://github.com/owner/repo/pull/42');
    expect($pr->diff_url)->toBe('https://github.com/owner/repo/pull/42.diff');
    expect($pr->patch_url)->toBe('https://github.com/owner/repo/pull/42.patch');
    expect($pr->base_ref)->toBe('main');
    expect($pr->head_ref)->toBe('feature-auth');
    expect($pr->draft)->toBeFalse();
    expect($pr->merged)->toBeFalse();
    expect($pr->merged_at)->toBeNull();
    expect($pr->merge_commit_sha)->toBeNull();
    expect($pr->comments)->toBe(5);
    expect($pr->review_comments)->toBe(10);
    expect($pr->commits)->toBe(3);
    expect($pr->additions)->toBe(150);
    expect($pr->deletions)->toBe(50);
    expect($pr->changed_files)->toBe(8);
    expect($pr->user)->toBeInstanceOf(GitUserData::class);
    expect($pr->user->login)->toBe('developer');
    expect($pr->merged_by)->toBeNull();
    expect($pr->created_at)->toBe('2024-01-15T10:00:00Z');
    expect($pr->updated_at)->toBe('2024-01-16T14:30:00Z');
    expect($pr->closed_at)->toBeNull();
});

it('can convert PullRequestDTO to array', function () {
    $pr = PullRequestDTO::fromApiResponse($this->sampleData);
    $array = $pr->toArray();

    expect($array['id'])->toBe(123456);
    expect($array['number'])->toBe(42);
    expect($array['state'])->toBe('open');
    expect($array['title'])->toBe('Add new feature');
    expect($array['base_ref'])->toBe('main');
    expect($array['head_ref'])->toBe('feature-auth');
    expect($array['user']['login'])->toBe('developer');
    expect($array['comments'])->toBe(5);
    expect($array['additions'])->toBe(150);
});

it('handles merged PR', function () {
    $mergedData = $this->sampleData;
    $mergedData['state'] = 'closed';
    $mergedData['merged'] = true;
    $mergedData['merged_at'] = '2024-01-17T09:00:00Z';
    $mergedData['merge_commit_sha'] = 'abc123def456';
    $mergedData['merged_by'] = $this->createMockUserData('maintainer', 2);
    $mergedData['closed_at'] = '2024-01-17T09:00:00Z';

    $pr = PullRequestDTO::fromApiResponse($mergedData);

    expect($pr->state)->toBe('closed');
    expect($pr->merged)->toBeTrue();
    expect($pr->merged_at)->toBe('2024-01-17T09:00:00Z');
    expect($pr->merge_commit_sha)->toBe('abc123def456');
    expect($pr->merged_by)->toBeInstanceOf(GitUserData::class);
    expect($pr->merged_by->login)->toBe('maintainer');
    expect($pr->closed_at)->toBe('2024-01-17T09:00:00Z');
});

it('handles draft PR', function () {
    $draftData = $this->sampleData;
    $draftData['draft'] = true;

    $pr = PullRequestDTO::fromApiResponse($draftData);

    expect($pr->draft)->toBeTrue();
});

it('handles empty body', function () {
    $dataWithEmptyBody = $this->sampleData;
    unset($dataWithEmptyBody['body']);

    $pr = PullRequestDTO::fromApiResponse($dataWithEmptyBody);

    expect($pr->body)->toBe('');
});

it('handles missing optional fields with defaults', function () {
    $minimalData = $this->sampleData;
    unset($minimalData['comments']);
    unset($minimalData['review_comments']);
    unset($minimalData['commits']);
    unset($minimalData['additions']);
    unset($minimalData['deletions']);
    unset($minimalData['changed_files']);
    unset($minimalData['draft']);
    unset($minimalData['merged']);

    $pr = PullRequestDTO::fromApiResponse($minimalData);

    expect($pr->comments)->toBe(0);
    expect($pr->review_comments)->toBe(0);
    expect($pr->commits)->toBe(0);
    expect($pr->additions)->toBe(0);
    expect($pr->deletions)->toBe(0);
    expect($pr->changed_files)->toBe(0);
    expect($pr->draft)->toBeFalse();
    expect($pr->merged)->toBeFalse();
});
