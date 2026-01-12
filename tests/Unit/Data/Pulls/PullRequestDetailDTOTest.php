<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDetailDTO;
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
        'comments' => 5,
        'review_comments' => 10,
        'commits' => 3,
        'additions' => 150,
        'deletions' => 50,
        'changed_files' => 8,
        'mergeable' => true,
        'mergeable_state' => 'clean',
        'rebaseable' => true,
    ];
});

it('can create PullRequestDetailDTO from detail response', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail)->toBeInstanceOf(PullRequestDetailDTO::class);
    expect($detail)->toBeInstanceOf(PullRequestSummaryDTO::class);
    expect($detail->id)->toBe(123456);
    expect($detail->number)->toBe(42);
    expect($detail->comments)->toBe(5);
    expect($detail->review_comments)->toBe(10);
    expect($detail->commits)->toBe(3);
    expect($detail->additions)->toBe(150);
    expect($detail->deletions)->toBe(50);
    expect($detail->changed_files)->toBe(8);
    expect($detail->mergeable)->toBeTrue();
    expect($detail->mergeable_state)->toBe('clean');
    expect($detail->rebaseable)->toBeTrue();
});

it('can convert PullRequestDetailDTO to array', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);
    $array = $detail->toArray();

    expect($array['id'])->toBe(123456);
    expect($array['comments'])->toBe(5);
    expect($array['review_comments'])->toBe(10);
    expect($array['commits'])->toBe(3);
    expect($array['additions'])->toBe(150);
    expect($array['deletions'])->toBe(50);
    expect($array['changed_files'])->toBe(8);
    expect($array['mergeable'])->toBeTrue();
    expect($array['mergeable_state'])->toBe('clean');
    expect($array['rebaseable'])->toBeTrue();
});

it('has detailed data returns true for detail', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail->hasDetailedData())->toBeTrue();
});

it('calculates total lines changed', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail->getTotalLinesChanged())->toBe(200);
});

it('calculates addition ratio', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail->getAdditionRatio())->toBe(0.75);
});

it('calculates addition ratio with zero changes', function () {
    $zeroData = $this->sampleData;
    $zeroData['additions'] = 0;
    $zeroData['deletions'] = 0;

    $detail = PullRequestDetailDTO::fromDetailResponse($zeroData);

    expect($detail->getAdditionRatio())->toBe(0.0);
});

it('detects if has comments', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail->hasComments())->toBeTrue();
});

it('detects if has no comments', function () {
    $noCommentsData = $this->sampleData;
    $noCommentsData['comments'] = 0;
    $noCommentsData['review_comments'] = 0;

    $detail = PullRequestDetailDTO::fromDetailResponse($noCommentsData);

    expect($detail->hasComments())->toBeFalse();
});

it('calculates total comments', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail->getTotalComments())->toBe(15);
});

it('detects ready to merge status', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail->isReadyToMerge())->toBeTrue();
});

it('detects not ready to merge when mergeable is false', function () {
    $notMergeableData = $this->sampleData;
    $notMergeableData['mergeable'] = false;

    $detail = PullRequestDetailDTO::fromDetailResponse($notMergeableData);

    expect($detail->isReadyToMerge())->toBeFalse();
});

it('detects merge conflicts', function () {
    $conflictData = $this->sampleData;
    $conflictData['mergeable'] = false;
    $conflictData['mergeable_state'] = 'dirty';

    $detail = PullRequestDetailDTO::fromDetailResponse($conflictData);

    expect($detail->hasMergeConflicts())->toBeTrue();
});

it('detects can rebase', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail->canRebase())->toBeTrue();
});

it('detects cannot rebase', function () {
    $noRebaseData = $this->sampleData;
    $noRebaseData['rebaseable'] = false;

    $detail = PullRequestDetailDTO::fromDetailResponse($noRebaseData);

    expect($detail->canRebase())->toBeFalse();
});

it('gets merge status description for clean', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);

    expect($detail->getMergeStatusDescription())->toBe('Ready to merge');
});

it('gets merge status description for dirty', function () {
    $dirtyData = $this->sampleData;
    $dirtyData['mergeable_state'] = 'dirty';

    $detail = PullRequestDetailDTO::fromDetailResponse($dirtyData);

    expect($detail->getMergeStatusDescription())->toBe('Has merge conflicts');
});

it('gets merge status description for unstable', function () {
    $unstableData = $this->sampleData;
    $unstableData['mergeable_state'] = 'unstable';

    $detail = PullRequestDetailDTO::fromDetailResponse($unstableData);

    expect($detail->getMergeStatusDescription())->toBe('Mergeable with failing checks');
});

it('gets merge status description for blocked', function () {
    $blockedData = $this->sampleData;
    $blockedData['mergeable_state'] = 'blocked';

    $detail = PullRequestDetailDTO::fromDetailResponse($blockedData);

    expect($detail->getMergeStatusDescription())->toBe('Blocked by branch protection');
});

it('gets merge status description for behind', function () {
    $behindData = $this->sampleData;
    $behindData['mergeable_state'] = 'behind';

    $detail = PullRequestDetailDTO::fromDetailResponse($behindData);

    expect($detail->getMergeStatusDescription())->toBe('Behind base branch');
});

it('gets merge status description for draft', function () {
    $draftData = $this->sampleData;
    $draftData['mergeable_state'] = 'draft';

    $detail = PullRequestDetailDTO::fromDetailResponse($draftData);

    expect($detail->getMergeStatusDescription())->toBe('Draft pull request');
});

it('gets merge status description for unknown', function () {
    $unknownData = $this->sampleData;
    $unknownData['mergeable'] = null;

    $detail = PullRequestDetailDTO::fromDetailResponse($unknownData);

    expect($detail->getMergeStatusDescription())->toBe('Merge status unknown (checking...)');
});

it('generates summary', function () {
    $detail = PullRequestDetailDTO::fromDetailResponse($this->sampleData);
    $summary = $detail->getSummary();

    expect($summary['pr'])->toBe('#42: Add new feature');
    expect($summary['stats']['comments'])->toBe(5);
    expect($summary['stats']['review_comments'])->toBe(10);
    expect($summary['stats']['commits'])->toBe(3);
    expect($summary['stats']['changes'])->toBe('+150/-50');
    expect($summary['stats']['files'])->toBe(8);
    expect($summary['merge_status']['mergeable'])->toBeTrue();
    expect($summary['merge_status']['description'])->toBe('Ready to merge');
    expect($summary['state'])->toBe('open');
    expect($summary['author'])->toBe('developer');
});

it('handles missing mergeable fields', function () {
    $missingData = $this->sampleData;
    unset($missingData['mergeable']);
    unset($missingData['mergeable_state']);
    unset($missingData['rebaseable']);

    $detail = PullRequestDetailDTO::fromDetailResponse($missingData);

    expect($detail->mergeable)->toBeNull();
    expect($detail->mergeable_state)->toBeNull();
    expect($detail->rebaseable)->toBeNull();
});
