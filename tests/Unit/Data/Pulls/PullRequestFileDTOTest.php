<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestFileDTO;

beforeEach(function () {
    $this->sampleData = [
        'sha' => 'abc123def456',
        'filename' => 'src/Controllers/AuthController.php',
        'status' => 'modified',
        'additions' => 50,
        'deletions' => 20,
        'changes' => 70,
        'blob_url' => 'https://github.com/owner/repo/blob/abc123/src/Controllers/AuthController.php',
        'raw_url' => 'https://github.com/owner/repo/raw/abc123/src/Controllers/AuthController.php',
        'contents_url' => 'https://api.github.com/repos/owner/repo/contents/src/Controllers/AuthController.php?ref=abc123',
        'patch' => '@@ -10,5 +10,25 @@\n+new code here',
    ];
});

it('can create PullRequestFileDTO from API response', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->sha)->toBe('abc123def456');
    expect($file->filename)->toBe('src/Controllers/AuthController.php');
    expect($file->status)->toBe('modified');
    expect($file->additions)->toBe(50);
    expect($file->deletions)->toBe(20);
    expect($file->changes)->toBe(70);
    expect($file->blob_url)->toBe('https://github.com/owner/repo/blob/abc123/src/Controllers/AuthController.php');
    expect($file->raw_url)->toBe('https://github.com/owner/repo/raw/abc123/src/Controllers/AuthController.php');
    expect($file->contents_url)->toBe('https://api.github.com/repos/owner/repo/contents/src/Controllers/AuthController.php?ref=abc123');
    expect($file->patch)->toBe('@@ -10,5 +10,25 @@\n+new code here');
    expect($file->previous_filename)->toBeNull();
});

it('can convert PullRequestFileDTO to array', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);
    $array = $file->toArray();

    expect($array['sha'])->toBe('abc123def456');
    expect($array['filename'])->toBe('src/Controllers/AuthController.php');
    expect($array['status'])->toBe('modified');
    expect($array['additions'])->toBe(50);
    expect($array['patch'])->toBe('@@ -10,5 +10,25 @@\n+new code here');
});

it('detects added file status', function () {
    $addedData = $this->sampleData;
    $addedData['status'] = 'added';

    $file = PullRequestFileDTO::fromApiResponse($addedData);

    expect($file->isAdded())->toBeTrue();
    expect($file->isModified())->toBeFalse();
    expect($file->isDeleted())->toBeFalse();
    expect($file->isRenamed())->toBeFalse();
});

it('detects deleted file status', function () {
    $deletedData = $this->sampleData;
    $deletedData['status'] = 'removed';

    $file = PullRequestFileDTO::fromApiResponse($deletedData);

    expect($file->isDeleted())->toBeTrue();
    expect($file->isAdded())->toBeFalse();
});

it('detects modified file status', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->isModified())->toBeTrue();
});

it('detects renamed file status', function () {
    $renamedData = $this->sampleData;
    $renamedData['status'] = 'renamed';
    $renamedData['previous_filename'] = 'src/Controllers/OldAuthController.php';

    $file = PullRequestFileDTO::fromApiResponse($renamedData);

    expect($file->isRenamed())->toBeTrue();
    expect($file->previous_filename)->toBe('src/Controllers/OldAuthController.php');
});

it('gets file extension', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->getExtension())->toBe('php');
});

it('gets directory path', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->getDirectory())->toBe('src/Controllers');
});

it('gets basename', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->getBasename())->toBe('AuthController');
});

it('calculates addition ratio', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->getAdditionRatio())->toBeGreaterThan(0.7);
});

it('calculates deletion ratio', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->getDeletionRatio())->toBeLessThan(0.3);
});

it('handles zero changes for ratios', function () {
    $zeroChanges = $this->sampleData;
    $zeroChanges['additions'] = 0;
    $zeroChanges['deletions'] = 0;
    $zeroChanges['changes'] = 0;

    $file = PullRequestFileDTO::fromApiResponse($zeroChanges);

    expect($file->getAdditionRatio())->toBe(0.0);
    expect($file->getDeletionRatio())->toBe(0.0);
});

it('detects large change', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->isLargeChange(50))->toBeTrue();
    expect($file->isLargeChange(100))->toBeFalse();
});

it('detects only additions', function () {
    $onlyAdditions = $this->sampleData;
    $onlyAdditions['additions'] = 50;
    $onlyAdditions['deletions'] = 0;

    $file = PullRequestFileDTO::fromApiResponse($onlyAdditions);

    expect($file->hasOnlyAdditions())->toBeTrue();
    expect($file->hasOnlyDeletions())->toBeFalse();
});

it('detects only deletions', function () {
    $onlyDeletions = $this->sampleData;
    $onlyDeletions['additions'] = 0;
    $onlyDeletions['deletions'] = 50;

    $file = PullRequestFileDTO::fromApiResponse($onlyDeletions);

    expect($file->hasOnlyDeletions())->toBeTrue();
    expect($file->hasOnlyAdditions())->toBeFalse();
});

it('detects file type', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);

    expect($file->getFileType())->toBe('php');
});

it('detects file type for JavaScript', function () {
    $jsData = $this->sampleData;
    $jsData['filename'] = 'src/app.js';

    $file = PullRequestFileDTO::fromApiResponse($jsData);

    expect($file->getFileType())->toBe('javascript');
});

it('detects file type for TypeScript', function () {
    $tsData = $this->sampleData;
    $tsData['filename'] = 'src/app.ts';

    $file = PullRequestFileDTO::fromApiResponse($tsData);

    expect($file->getFileType())->toBe('typescript');
});

it('detects file type for Dockerfile', function () {
    $dockerData = $this->sampleData;
    $dockerData['filename'] = 'Dockerfile';

    $file = PullRequestFileDTO::fromApiResponse($dockerData);

    expect($file->getFileType())->toBe('docker');
});

it('detects test file', function () {
    $testData = $this->sampleData;
    $testData['filename'] = 'tests/Unit/AuthTest.php';

    $file = PullRequestFileDTO::fromApiResponse($testData);

    expect($file->isTestFile())->toBeTrue();
});

it('detects config file', function () {
    $configData = $this->sampleData;
    $configData['filename'] = 'config/app.php';

    $file = PullRequestFileDTO::fromApiResponse($configData);

    expect($file->isConfigFile())->toBeTrue();
});

it('detects documentation file', function () {
    $docData = $this->sampleData;
    $docData['filename'] = 'README.md';

    $file = PullRequestFileDTO::fromApiResponse($docData);

    expect($file->isDocumentationFile())->toBeTrue();
});

it('generates summary', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);
    $summary = $file->getSummary();

    expect($summary['file'])->toBe('src/Controllers/AuthController.php');
    expect($summary['status'])->toBe('modified');
    expect($summary['changes'])->toBe('+50/-20');
    expect($summary['type'])->toBe('php');
});

it('generates analysis tags', function () {
    $file = PullRequestFileDTO::fromApiResponse($this->sampleData);
    $tags = $file->getAnalysisTags();

    expect($tags)->toContain('php');
    expect($tags)->toContain('modified');
});

it('generates analysis tags for test file', function () {
    $testData = $this->sampleData;
    $testData['filename'] = 'tests/Unit/AuthTest.php';
    $testData['changes'] = 150;

    $file = PullRequestFileDTO::fromApiResponse($testData);
    $tags = $file->getAnalysisTags();

    expect($tags)->toContain('test');
    expect($tags)->toContain('large-change');
});
