<?php

use Carbon\Carbon;
use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Releases\ReleaseData;

beforeEach(function () {
    $this->sampleData = [
        'url' => 'https://api.github.com/repos/owner/repo/releases/1',
        'assets_url' => 'https://api.github.com/repos/owner/repo/releases/1/assets',
        'upload_url' => 'https://uploads.github.com/repos/owner/repo/releases/1/assets{?name,label}',
        'html_url' => 'https://github.com/owner/repo/releases/tag/v1.0.0',
        'id' => 12345,
        'author' => $this->createMockUserData('releaser', 1),
        'node_id' => 'RE_release123',
        'tag_name' => 'v1.0.0',
        'target_commitish' => 'main',
        'name' => 'Version 1.0.0',
        'draft' => false,
        'prerelease' => false,
        'created_at' => '2024-01-15T10:00:00Z',
        'published_at' => '2024-01-15T12:00:00Z',
        'assets' => [
            [
                'name' => 'package.zip',
                'content_type' => 'application/zip',
                'size' => 1024,
            ],
        ],
        'tarball_url' => 'https://api.github.com/repos/owner/repo/tarball/v1.0.0',
        'zipball_url' => 'https://api.github.com/repos/owner/repo/zipball/v1.0.0',
        'body' => '## Changelog\n\n- Added new feature\n- Fixed bugs',
    ];
});

it('can create ReleaseData from array', function () {
    $release = ReleaseData::fromArray($this->sampleData);

    expect($release->url)->toBe('https://api.github.com/repos/owner/repo/releases/1');
    expect($release->assets_url)->toBe('https://api.github.com/repos/owner/repo/releases/1/assets');
    expect($release->upload_url)->toBe('https://uploads.github.com/repos/owner/repo/releases/1/assets{?name,label}');
    expect($release->html_url)->toBe('https://github.com/owner/repo/releases/tag/v1.0.0');
    expect($release->id)->toBe(12345);
    expect($release->author)->toBeInstanceOf(GitUserData::class);
    expect($release->author->login)->toBe('releaser');
    expect($release->node_id)->toBe('RE_release123');
    expect($release->tag_name)->toBe('v1.0.0');
    expect($release->target_commitish)->toBe('main');
    expect($release->name)->toBe('Version 1.0.0');
    expect($release->draft)->toBeFalse();
    expect($release->prerelease)->toBeFalse();
    expect($release->created_at)->toBeInstanceOf(Carbon::class);
    expect($release->published_at)->toBeInstanceOf(Carbon::class);
    expect($release->assets)->toBeArray();
    expect($release->assets)->toHaveCount(1);
    expect($release->tarball_url)->toBe('https://api.github.com/repos/owner/repo/tarball/v1.0.0');
    expect($release->zipball_url)->toBe('https://api.github.com/repos/owner/repo/zipball/v1.0.0');
    expect($release->body)->toBe('## Changelog\n\n- Added new feature\n- Fixed bugs');
});

it('can convert ReleaseData to array', function () {
    $release = ReleaseData::fromArray($this->sampleData);
    $array = $release->toArray();

    expect($array['url'])->toBe('https://api.github.com/repos/owner/repo/releases/1');
    expect($array['id'])->toBe(12345);
    expect($array['author'])->toBeArray();
    expect($array['author']['login'])->toBe('releaser');
    expect($array['tag_name'])->toBe('v1.0.0');
    expect($array['name'])->toBe('Version 1.0.0');
    expect($array['draft'])->toBeFalse();
    expect($array['prerelease'])->toBeFalse();
    expect($array['created_at'])->toBeString();
    expect($array['published_at'])->toBeString();
});

it('handles draft release', function () {
    $draftData = $this->sampleData;
    $draftData['draft'] = true;

    $release = ReleaseData::fromArray($draftData);

    expect($release->draft)->toBeTrue();
});

it('handles prerelease', function () {
    $prereleaseData = $this->sampleData;
    $prereleaseData['prerelease'] = true;

    $release = ReleaseData::fromArray($prereleaseData);

    expect($release->prerelease)->toBeTrue();
});

it('handles null body', function () {
    $noBodyData = $this->sampleData;
    unset($noBodyData['body']);

    $release = ReleaseData::fromArray($noBodyData);

    expect($release->body)->toBeNull();
});

it('handles empty assets', function () {
    $noAssetsData = $this->sampleData;
    unset($noAssetsData['assets']);

    $release = ReleaseData::fromArray($noAssetsData);

    expect($release->assets)->toBe([]);
});

it('handles discussion_url', function () {
    $discussionData = $this->sampleData;
    $discussionData['discussion_url'] = 'https://github.com/owner/repo/discussions/1';

    $release = ReleaseData::fromArray($discussionData);

    expect($release->discussion_url)->toBe('https://github.com/owner/repo/discussions/1');
});

it('handles make_latest flag', function () {
    $latestData = $this->sampleData;
    $latestData['make_latest'] = true;

    $release = ReleaseData::fromArray($latestData);

    expect($release->make_latest)->toBeTrue();
});

it('handles reactions', function () {
    $reactionsData = $this->sampleData;
    $reactionsData['reactions'] = [
        'url' => 'https://api.github.com/repos/owner/repo/releases/1/reactions',
        '+1' => 10,
        'hooray' => 5,
    ];

    $release = ReleaseData::fromArray($reactionsData);

    expect($release->reactions)->toBeArray();
    expect($release->reactions['+1'])->toBe(10);
});

it('handles multiple assets', function () {
    $multipleAssetsData = $this->sampleData;
    $multipleAssetsData['assets'] = [
        ['name' => 'package-linux.tar.gz', 'size' => 2048],
        ['name' => 'package-macos.tar.gz', 'size' => 2048],
        ['name' => 'package-windows.zip', 'size' => 3072],
    ];

    $release = ReleaseData::fromArray($multipleAssetsData);

    expect($release->assets)->toHaveCount(3);
});

it('parses dates correctly', function () {
    $release = ReleaseData::fromArray($this->sampleData);

    expect($release->created_at->format('Y-m-d'))->toBe('2024-01-15');
    expect($release->published_at->format('H:i:s'))->toBe('12:00:00');
});
