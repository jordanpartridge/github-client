<?php

use JordanPartridge\GithubClient\Data\TreeData;

it('can create TreeData from array', function () {
    $data = [
        'sha' => 'abc123def456789',
        'url' => 'https://api.github.com/repos/owner/repo/git/trees/abc123def456789',
    ];

    $tree = TreeData::fromArray($data);

    expect($tree->sha)->toBe('abc123def456789');
    expect($tree->url)->toBe('https://api.github.com/repos/owner/repo/git/trees/abc123def456789');
});

it('can convert TreeData to array', function () {
    $tree = new TreeData(
        sha: 'xyz789',
        url: 'https://api.github.com/repos/owner/repo/git/trees/xyz789',
    );

    $array = $tree->toArray();

    expect($array)->toBe([
        'sha' => 'xyz789',
        'url' => 'https://api.github.com/repos/owner/repo/git/trees/xyz789',
    ]);
});

it('handles various SHA formats', function () {
    $shas = [
        'abc123def456789',
        'a' . str_repeat('0', 39),
        str_repeat('f', 40),
    ];

    foreach ($shas as $sha) {
        $data = [
            'sha' => $sha,
            'url' => "https://api.github.com/repos/owner/repo/git/trees/{$sha}",
        ];

        $tree = TreeData::fromArray($data);
        expect($tree->sha)->toBe($sha);
    }
});

it('preserves URL format', function () {
    $url = 'https://api.github.com/repos/special-org/my-repo/git/trees/abc123';

    $tree = TreeData::fromArray([
        'sha' => 'abc123',
        'url' => $url,
    ]);

    expect($tree->url)->toBe($url);
});
