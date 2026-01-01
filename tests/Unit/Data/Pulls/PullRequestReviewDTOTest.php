<?php

use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestReviewDTO;

beforeEach(function () {
    $this->sampleData = [
        'id' => 987654,
        'node_id' => 'PRR_review123',
        'user' => $this->createMockUserData('reviewer', 5),
        'body' => 'Looks good overall, just a few minor suggestions.',
        'state' => 'APPROVED',
        'html_url' => 'https://github.com/owner/repo/pull/42#pullrequestreview-987654',
        'pull_request_url' => 'https://api.github.com/repos/owner/repo/pulls/42',
        'commit_id' => 'abc123def456',
        'submitted_at' => '2024-01-16T14:00:00Z',
    ];
});

it('can create PullRequestReviewDTO from array', function () {
    $review = PullRequestReviewDTO::fromArray($this->sampleData);

    expect($review->id)->toBe(987654);
    expect($review->node_id)->toBe('PRR_review123');
    expect($review->user)->toBeInstanceOf(GitUserData::class);
    expect($review->user->login)->toBe('reviewer');
    expect($review->body)->toBe('Looks good overall, just a few minor suggestions.');
    expect($review->state)->toBe('APPROVED');
    expect($review->html_url)->toBe('https://github.com/owner/repo/pull/42#pullrequestreview-987654');
    expect($review->pull_request_url)->toBe('https://api.github.com/repos/owner/repo/pulls/42');
    expect($review->commit_id)->toBe('abc123def456');
    expect($review->submitted_at)->toBe('2024-01-16T14:00:00Z');
});

it('can create PullRequestReviewDTO from API response', function () {
    $review = PullRequestReviewDTO::fromApiResponse($this->sampleData);

    expect($review->id)->toBe(987654);
    expect($review->state)->toBe('APPROVED');
});

it('can convert PullRequestReviewDTO to array', function () {
    $review = PullRequestReviewDTO::fromArray($this->sampleData);
    $array = $review->toArray();

    expect($array['id'])->toBe(987654);
    expect($array['node_id'])->toBe('PRR_review123');
    expect($array['user']['login'])->toBe('reviewer');
    expect($array['body'])->toBe('Looks good overall, just a few minor suggestions.');
    expect($array['state'])->toBe('APPROVED');
    expect($array['commit_id'])->toBe('abc123def456');
    expect($array['submitted_at'])->toBe('2024-01-16T14:00:00Z');
});

it('handles CHANGES_REQUESTED state', function () {
    $changesData = $this->sampleData;
    $changesData['state'] = 'CHANGES_REQUESTED';
    $changesData['body'] = 'Please fix the issues mentioned in the comments.';

    $review = PullRequestReviewDTO::fromArray($changesData);

    expect($review->state)->toBe('CHANGES_REQUESTED');
});

it('handles COMMENTED state', function () {
    $commentedData = $this->sampleData;
    $commentedData['state'] = 'COMMENTED';

    $review = PullRequestReviewDTO::fromArray($commentedData);

    expect($review->state)->toBe('COMMENTED');
});

it('handles PENDING state', function () {
    $pendingData = $this->sampleData;
    $pendingData['state'] = 'PENDING';

    $review = PullRequestReviewDTO::fromArray($pendingData);

    expect($review->state)->toBe('PENDING');
});

it('handles empty body', function () {
    $emptyBodyData = $this->sampleData;
    unset($emptyBodyData['body']);

    $review = PullRequestReviewDTO::fromArray($emptyBodyData);

    expect($review->body)->toBe('');
});
