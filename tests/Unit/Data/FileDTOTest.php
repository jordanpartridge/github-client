<?php

use JordanPartridge\GithubClient\Data\FileDTO;

it('can create FileDTO from array', function () {
    $data = [
        'sha' => 'abc123def456',
        'filename' => 'src/Example.php',
        'status' => 'modified',
        'additions' => 10,
        'deletions' => 5,
        'changes' => 15,
        'raw_url' => 'https://github.com/owner/repo/raw/abc123/src/Example.php',
        'contents_url' => 'https://api.github.com/repos/owner/repo/contents/src/Example.php?ref=abc123',
        'blob_url' => 'https://github.com/owner/repo/blob/abc123/src/Example.php',
        'patch' => '@@ -1,5 +1,10 @@\n+new line',
        'size' => 1024,
    ];

    $file = FileDTO::fromArray($data);

    expect($file->sha)->toBe('abc123def456');
    expect($file->filename)->toBe('src/Example.php');
    expect($file->status)->toBe('modified');
    expect($file->additions)->toBe(10);
    expect($file->deletions)->toBe(5);
    expect($file->changes)->toBe(15);
    expect($file->raw_url)->toBe('https://github.com/owner/repo/raw/abc123/src/Example.php');
    expect($file->contents_url)->toBe('https://api.github.com/repos/owner/repo/contents/src/Example.php?ref=abc123');
    expect($file->blob_url)->toBe('https://github.com/owner/repo/blob/abc123/src/Example.php');
    expect($file->patch)->toBe('@@ -1,5 +1,10 @@\n+new line');
    expect($file->size)->toBe(1024);
});

it('can convert FileDTO to array', function () {
    $file = new FileDTO(
        sha: 'xyz789',
        filename: 'test.php',
        status: 'added',
        additions: 20,
        deletions: 0,
        changes: 20,
        raw_url: 'https://github.com/raw',
        contents_url: 'https://api.github.com/contents',
        blob_url: 'https://github.com/blob',
        patch: '@@ +new code',
        size: 512,
    );

    $array = $file->toArray();

    expect($array['sha'])->toBe('xyz789');
    expect($array['filename'])->toBe('test.php');
    expect($array['status'])->toBe('added');
    expect($array['additions'])->toBe(20);
    expect($array['deletions'])->toBe(0);
    expect($array['changes'])->toBe(20);
    expect($array['patch'])->toBe('@@ +new code');
    expect($array['size'])->toBe(512);
});

it('handles optional fields with defaults', function () {
    $data = [
        'sha' => 'abc123',
        'filename' => 'file.php',
        'status' => 'modified',
    ];

    $file = FileDTO::fromArray($data);

    expect($file->additions)->toBe(0);
    expect($file->deletions)->toBe(0);
    expect($file->changes)->toBe(0);
    expect($file->raw_url)->toBe('');
    expect($file->contents_url)->toBe('');
    expect($file->blob_url)->toBe('');
    expect($file->patch)->toBeNull();
    expect($file->size)->toBeNull();
});

it('handles null patch', function () {
    $data = [
        'sha' => 'abc123',
        'filename' => 'binary.png',
        'status' => 'added',
    ];

    $file = FileDTO::fromArray($data);

    expect($file->patch)->toBeNull();
});

it('handles null size', function () {
    $data = [
        'sha' => 'abc123',
        'filename' => 'file.php',
        'status' => 'modified',
    ];

    $file = FileDTO::fromArray($data);

    expect($file->size)->toBeNull();
});

it('handles deleted file status', function () {
    $data = [
        'sha' => 'abc123',
        'filename' => 'deleted.php',
        'status' => 'removed',
        'deletions' => 50,
    ];

    $file = FileDTO::fromArray($data);

    expect($file->status)->toBe('removed');
    expect($file->deletions)->toBe(50);
});

it('handles renamed file status', function () {
    $data = [
        'sha' => 'abc123',
        'filename' => 'newname.php',
        'status' => 'renamed',
    ];

    $file = FileDTO::fromArray($data);

    expect($file->status)->toBe('renamed');
});
