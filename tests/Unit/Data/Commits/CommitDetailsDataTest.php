<?php

use JordanPartridge\GithubClient\Data\Commits\CommitAuthorData;
use JordanPartridge\GithubClient\Data\Commits\CommitDetailsData;
use JordanPartridge\GithubClient\Data\FileDTO;
use JordanPartridge\GithubClient\Data\TreeData;
use JordanPartridge\GithubClient\Data\VerificationData;

beforeEach(function () {
    $this->sampleData = [
        'author' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'date' => '2024-01-15T10:30:00Z',
        ],
        'committer' => [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'date' => '2024-01-15T10:35:00Z',
        ],
        'message' => 'Fix bug in authentication',
        'tree' => [
            'sha' => 'tree123sha',
            'url' => 'https://api.github.com/repos/owner/repo/git/trees/tree123sha',
        ],
        'url' => 'https://api.github.com/repos/owner/repo/git/commits/abc123',
        'comment_count' => 5,
        'verification' => [
            'verified' => true,
            'reason' => 'valid',
            'signature' => 'gpg-signature',
            'payload' => 'commit-payload',
            'verified_at' => '2024-01-15T10:30:00Z',
        ],
    ];
});

it('can create CommitDetailsData from array', function () {
    $details = CommitDetailsData::fromArray($this->sampleData);

    expect($details->author)->toBeInstanceOf(CommitAuthorData::class);
    expect($details->author->name)->toBe('John Doe');
    expect($details->committer)->toBeInstanceOf(CommitAuthorData::class);
    expect($details->committer->name)->toBe('Jane Doe');
    expect($details->message)->toBe('Fix bug in authentication');
    expect($details->tree)->toBeInstanceOf(TreeData::class);
    expect($details->tree->sha)->toBe('tree123sha');
    expect($details->url)->toBe('https://api.github.com/repos/owner/repo/git/commits/abc123');
    expect($details->comment_count)->toBe(5);
    expect($details->verification)->toBeInstanceOf(VerificationData::class);
    expect($details->verification->verified)->toBeTrue();
});

it('can convert CommitDetailsData to array', function () {
    $details = CommitDetailsData::fromArray($this->sampleData);
    $array = $details->toArray();

    expect($array['author']['name'])->toBe('John Doe');
    expect($array['committer']['name'])->toBe('Jane Doe');
    expect($array['message'])->toBe('Fix bug in authentication');
    expect($array['tree']['sha'])->toBe('tree123sha');
    expect($array['comment_count'])->toBe(5);
    expect($array['verification']['verified'])->toBeTrue();
});

it('handles files array when present', function () {
    $dataWithFiles = array_merge($this->sampleData, [
        'files' => [
            [
                'sha' => 'file123',
                'filename' => 'src/Example.php',
                'status' => 'modified',
                'additions' => 10,
                'deletions' => 5,
                'changes' => 15,
            ],
        ],
    ]);

    $details = CommitDetailsData::fromArray($dataWithFiles);

    expect($details->files)->toBeArray();
    expect($details->files)->toHaveCount(1);
    expect($details->files[0])->toBeInstanceOf(FileDTO::class);
    expect($details->files[0]->filename)->toBe('src/Example.php');
});

it('handles null files array', function () {
    $details = CommitDetailsData::fromArray($this->sampleData);

    expect($details->files)->toBeNull();
});
