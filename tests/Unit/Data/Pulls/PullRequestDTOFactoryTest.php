<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDetailDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTOFactory;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestSummaryDTO;

beforeEach(function () {
    $this->listResponseData = [
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

    $this->detailResponseData = array_merge($this->listResponseData, [
        'comments' => 5,
        'review_comments' => 10,
        'commits' => 3,
        'additions' => 150,
        'deletions' => 50,
        'changed_files' => 8,
        'mergeable' => true,
        'mergeable_state' => 'clean',
        'rebaseable' => true,
    ]);
});

it('creates summary DTO from list response', function () {
    $dto = PullRequestDTOFactory::fromResponse($this->listResponseData);

    expect($dto)->toBeInstanceOf(PullRequestSummaryDTO::class);
    expect($dto)->not->toBeInstanceOf(PullRequestDetailDTO::class);
});

it('creates detail DTO from detail response', function () {
    $dto = PullRequestDTOFactory::fromResponse($this->detailResponseData);

    expect($dto)->toBeInstanceOf(PullRequestDetailDTO::class);
});

it('creates DTOs from response array', function () {
    $dataArray = [
        $this->listResponseData,
        $this->detailResponseData,
    ];

    $dtos = PullRequestDTOFactory::fromResponseArray($dataArray);

    expect($dtos)->toHaveCount(2);
    expect($dtos[0])->toBeInstanceOf(PullRequestSummaryDTO::class);
    expect($dtos[1])->toBeInstanceOf(PullRequestDetailDTO::class);
});

it('forces creation of summary DTO', function () {
    $dto = PullRequestDTOFactory::createSummary($this->detailResponseData);

    expect($dto)->toBeInstanceOf(PullRequestSummaryDTO::class);
    expect($dto)->not->toBeInstanceOf(PullRequestDetailDTO::class);
});

it('forces creation of detail DTO', function () {
    $dto = PullRequestDTOFactory::createDetail($this->detailResponseData);

    expect($dto)->toBeInstanceOf(PullRequestDetailDTO::class);
});

it('analyzes list response correctly', function () {
    $analysis = PullRequestDTOFactory::analyzeResponse($this->listResponseData);

    expect($analysis['would_create'])->toBe('PullRequestSummaryDTO');
    expect($analysis['has_detailed_fields'])->toBeFalse();
    expect($analysis['detail_fields_present'])->toBeEmpty();
});

it('analyzes detail response correctly', function () {
    $analysis = PullRequestDTOFactory::analyzeResponse($this->detailResponseData);

    expect($analysis['would_create'])->toBe('PullRequestDetailDTO');
    expect($analysis['has_detailed_fields'])->toBeTrue();
    expect($analysis['detail_fields_present'])->toContain('comments');
    expect($analysis['detail_fields_present'])->toContain('additions');
    expect($analysis['detail_fields_present'])->toContain('deletions');
});

it('detects detail response with partial fields', function () {
    $partialData = $this->listResponseData;
    $partialData['comments'] = 5;
    $partialData['additions'] = 100;

    $dto = PullRequestDTOFactory::fromResponse($partialData);

    expect($dto)->toBeInstanceOf(PullRequestDetailDTO::class);
});

it('treats response as summary when only one detail field present', function () {
    $oneFieldData = $this->listResponseData;
    $oneFieldData['comments'] = 5;

    $dto = PullRequestDTOFactory::fromResponse($oneFieldData);

    expect($dto)->toBeInstanceOf(PullRequestSummaryDTO::class);
    expect($dto)->not->toBeInstanceOf(PullRequestDetailDTO::class);
});
