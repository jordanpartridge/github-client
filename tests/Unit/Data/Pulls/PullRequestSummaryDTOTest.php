<?php

use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestSummaryDTO;

beforeEach(function () {
    $this->sampleData = [
        'id' => 123456,
        'number' => 42,
        'state' => 'open',
        'title' => 'Add new feature',
        'body' => 'This PR adds a new feature.',
        'html_url' => 'https://github.com/owner/repo/pull/42',
        'diff_url' => 'https://github.com/owner/repo/pull/42.diff',
        'patch_url' => 'https://github.com/owner/repo/pull/42.patch',
        'base' => ['ref' => 'main'],
        'head' => ['ref' => 'feature-branch'],
        'draft' => false,
        'merged' => false,
        'merged_at' => null,
        'merge_commit_sha' => null,
        'user' => $this->createMockUserData('developer', 1),
        'merged_by' => null,
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-16T14:30:00Z',
        'closed_at' => null,
    ];
});

it('can create PullRequestSummaryDTO from list response', function () {
    $summary = PullRequestSummaryDTO::fromListResponse($this->sampleData);

    expect($summary->id)->toBe(123456);
    expect($summary->number)->toBe(42);
    expect($summary->state)->toBe('open');
    expect($summary->title)->toBe('Add new feature');
    expect($summary->body)->toBe('This PR adds a new feature.');
    expect($summary->html_url)->toBe('https://github.com/owner/repo/pull/42');
    expect($summary->diff_url)->toBe('https://github.com/owner/repo/pull/42.diff');
    expect($summary->patch_url)->toBe('https://github.com/owner/repo/pull/42.patch');
    expect($summary->base_ref)->toBe('main');
    expect($summary->head_ref)->toBe('feature-branch');
    expect($summary->draft)->toBeFalse();
    expect($summary->merged)->toBeFalse();
    expect($summary->merged_at)->toBeNull();
    expect($summary->merge_commit_sha)->toBeNull();
    expect($summary->user)->toBeInstanceOf(GitUserData::class);
    expect($summary->user->login)->toBe('developer');
    expect($summary->merged_by)->toBeNull();
    expect($summary->created_at)->toBe('2024-01-15T10:00:00Z');
    expect($summary->updated_at)->toBe('2024-01-16T14:30:00Z');
    expect($summary->closed_at)->toBeNull();
});

it('can convert PullRequestSummaryDTO to array', function () {
    $summary = PullRequestSummaryDTO::fromListResponse($this->sampleData);
    $array = $summary->toArray();

    expect($array['id'])->toBe(123456);
    expect($array['number'])->toBe(42);
    expect($array['state'])->toBe('open');
    expect($array['title'])->toBe('Add new feature');
    expect($array['base_ref'])->toBe('main');
    expect($array['head_ref'])->toBe('feature-branch');
    expect($array['user']['login'])->toBe('developer');
    expect($array['merged_by'])->toBeNull();
});

it('has detailed data returns false for summary', function () {
    $summary = PullRequestSummaryDTO::fromListResponse($this->sampleData);

    expect($summary->hasDetailedData())->toBeFalse();
});

it('handles merged PR summary', function () {
    $mergedData = $this->sampleData;
    $mergedData['state'] = 'closed';
    $mergedData['merged'] = true;
    $mergedData['merged_at'] = '2024-01-17T09:00:00Z';
    $mergedData['merge_commit_sha'] = 'abc123def456';
    $mergedData['merged_by'] = $this->createMockUserData('maintainer', 2);
    $mergedData['closed_at'] = '2024-01-17T09:00:00Z';

    $summary = PullRequestSummaryDTO::fromListResponse($mergedData);

    expect($summary->state)->toBe('closed');
    expect($summary->merged)->toBeTrue();
    expect($summary->merged_at)->toBe('2024-01-17T09:00:00Z');
    expect($summary->merge_commit_sha)->toBe('abc123def456');
    expect($summary->merged_by)->toBeInstanceOf(GitUserData::class);
    expect($summary->merged_by->login)->toBe('maintainer');
});

it('handles draft PR summary', function () {
    $draftData = $this->sampleData;
    $draftData['draft'] = true;

    $summary = PullRequestSummaryDTO::fromListResponse($draftData);

    expect($summary->draft)->toBeTrue();
});

it('handles empty body', function () {
    $noBodyData = $this->sampleData;
    unset($noBodyData['body']);

    $summary = PullRequestSummaryDTO::fromListResponse($noBodyData);

    expect($summary->body)->toBe('');
});
