<?php

use JordanPartridge\GithubClient\Data\Commits\CommitData;
use JordanPartridge\GithubClient\Data\Commits\CommitDetailsData;
use JordanPartridge\GithubClient\Data\Commits\CommitFileData;
use JordanPartridge\GithubClient\Data\Commits\CommitStatsData;
use JordanPartridge\GithubClient\Data\GitUserData;

beforeEach(function () {
    $this->sampleData = [
        'sha' => 'abc123def456789',
        'node_id' => 'C_commit123',
        'commit' => [
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
                'verified_at' => null,
            ],
        ],
        'url' => 'https://api.github.com/repos/owner/repo/commits/abc123',
        'html_url' => 'https://github.com/owner/repo/commit/abc123',
        'comments_url' => 'https://api.github.com/repos/owner/repo/commits/abc123/comments',
        'author' => $this->createMockUserData('johndoe', 1),
        'committer' => $this->createMockUserData('janedoe', 2),
        'parents' => [
            ['sha' => 'parent123', 'url' => 'https://api.github.com/repos/owner/repo/commits/parent123'],
        ],
    ];
});

it('can create CommitData from array', function () {
    $commit = CommitData::fromArray($this->sampleData);

    expect($commit->sha)->toBe('abc123def456789');
    expect($commit->node_id)->toBe('C_commit123');
    expect($commit->commit)->toBeInstanceOf(CommitDetailsData::class);
    expect($commit->commit->message)->toBe('Fix bug in authentication');
    expect($commit->url)->toBe('https://api.github.com/repos/owner/repo/commits/abc123');
    expect($commit->html_url)->toBe('https://github.com/owner/repo/commit/abc123');
    expect($commit->comments_url)->toBe('https://api.github.com/repos/owner/repo/commits/abc123/comments');
    expect($commit->author)->toBeInstanceOf(GitUserData::class);
    expect($commit->author->login)->toBe('johndoe');
    expect($commit->committer)->toBeInstanceOf(GitUserData::class);
    expect($commit->committer->login)->toBe('janedoe');
    expect($commit->parents)->toBeArray();
    expect($commit->parents)->toHaveCount(1);
});

it('can convert CommitData to array', function () {
    $commit = CommitData::fromArray($this->sampleData);
    $array = $commit->toArray();

    expect($array['sha'])->toBe('abc123def456789');
    expect($array['node_id'])->toBe('C_commit123');
    expect($array['commit'])->toBeArray();
    expect($array['commit']['message'])->toBe('Fix bug in authentication');
    expect($array['author']['login'])->toBe('johndoe');
    expect($array['committer']['login'])->toBe('janedoe');
    expect($array['parents'])->toHaveCount(1);
});

it('handles null author and committer', function () {
    $dataWithNullUsers = $this->sampleData;
    unset($dataWithNullUsers['author']);
    unset($dataWithNullUsers['committer']);

    $commit = CommitData::fromArray($dataWithNullUsers);

    expect($commit->author)->toBeNull();
    expect($commit->committer)->toBeNull();
});

it('handles stats when present', function () {
    $dataWithStats = array_merge($this->sampleData, [
        'stats' => [
            'total' => 150,
            'additions' => 100,
            'deletions' => 50,
        ],
    ]);

    $commit = CommitData::fromArray($dataWithStats);

    expect($commit->stats)->toBeInstanceOf(CommitStatsData::class);
    expect($commit->stats->total)->toBe(150);
    expect($commit->stats->additions)->toBe(100);
    expect($commit->stats->deletions)->toBe(50);
});

it('handles files array when present', function () {
    $dataWithFiles = array_merge($this->sampleData, [
        'files' => [
            [
                'filename' => 'src/Example.php',
                'status' => 'modified',
                'additions' => 10,
                'deletions' => 5,
                'changes' => 15,
                'blob_url' => 'https://github.com/blob',
                'raw_url' => 'https://github.com/raw',
                'contents_url' => 'https://api.github.com/contents',
            ],
        ],
    ]);

    $commit = CommitData::fromArray($dataWithFiles);

    expect($commit->files)->toBeArray();
    expect($commit->files)->toHaveCount(1);
    expect($commit->files[0])->toBeInstanceOf(CommitFileData::class);
    expect($commit->files[0]->filename)->toBe('src/Example.php');
});

it('handles empty parents array', function () {
    $dataWithNoParents = $this->sampleData;
    unset($dataWithNoParents['parents']);

    $commit = CommitData::fromArray($dataWithNoParents);

    expect($commit->parents)->toBe([]);
});
