<?php

use JordanPartridge\GithubClient\Data\Commits\CommitFileData;

it('can create CommitFileData from array', function () {
    $data = [
        'filename' => 'src/Example.php',
        'status' => 'modified',
        'additions' => 10,
        'deletions' => 5,
        'changes' => 15,
        'blob_url' => 'https://github.com/owner/repo/blob/abc123/src/Example.php',
        'raw_url' => 'https://github.com/owner/repo/raw/abc123/src/Example.php',
        'contents_url' => 'https://api.github.com/repos/owner/repo/contents/src/Example.php?ref=abc123',
        'patch' => '@@ -1,5 +1,10 @@\n+new line',
        'sha' => 'abc123def456',
    ];

    $file = CommitFileData::fromArray($data);

    expect($file->filename)->toBe('src/Example.php');
    expect($file->status)->toBe('modified');
    expect($file->additions)->toBe(10);
    expect($file->deletions)->toBe(5);
    expect($file->changes)->toBe(15);
    expect($file->blob_url)->toBe('https://github.com/owner/repo/blob/abc123/src/Example.php');
    expect($file->raw_url)->toBe('https://github.com/owner/repo/raw/abc123/src/Example.php');
    expect($file->contents_url)->toBe('https://api.github.com/repos/owner/repo/contents/src/Example.php?ref=abc123');
    expect($file->patch)->toBe('@@ -1,5 +1,10 @@\n+new line');
    expect($file->sha)->toBe('abc123def456');
});

it('can convert CommitFileData to array', function () {
    $file = new CommitFileData(
        filename: 'test.php',
        status: 'added',
        additions: 20,
        deletions: 0,
        changes: 20,
        blob_url: 'https://github.com/blob',
        raw_url: 'https://github.com/raw',
        contents_url: 'https://api.github.com/contents',
        patch: '@@ -0,0 +1,20 @@',
        sha: 'def789',
    );

    $array = $file->toArray();

    expect($array['filename'])->toBe('test.php');
    expect($array['status'])->toBe('added');
    expect($array['additions'])->toBe(20);
    expect($array['deletions'])->toBe(0);
    expect($array['changes'])->toBe(20);
    expect($array['patch'])->toBe('@@ -0,0 +1,20 @@');
    expect($array['sha'])->toBe('def789');
});

it('handles optional fields as null', function () {
    $data = [
        'filename' => 'deleted.php',
        'status' => 'removed',
        'additions' => 0,
        'deletions' => 50,
        'changes' => 50,
        'blob_url' => 'https://github.com/blob',
        'raw_url' => 'https://github.com/raw',
        'contents_url' => 'https://api.github.com/contents',
    ];

    $file = CommitFileData::fromArray($data);

    expect($file->patch)->toBeNull();
    expect($file->sha)->toBeNull();
});
