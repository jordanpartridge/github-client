<?php

use JordanPartridge\GithubClient\Data\Commits\CommitStatsData;

it('can create CommitStatsData from array', function () {
    $data = [
        'total' => 150,
        'additions' => 100,
        'deletions' => 50,
    ];

    $stats = CommitStatsData::fromArray($data);

    expect($stats->total)->toBe(150);
    expect($stats->additions)->toBe(100);
    expect($stats->deletions)->toBe(50);
});

it('can convert CommitStatsData to array', function () {
    $stats = new CommitStatsData(
        total: 200,
        additions: 150,
        deletions: 50,
    );

    $array = $stats->toArray();

    expect($array)->toBe([
        'total' => 200,
        'additions' => 150,
        'deletions' => 50,
    ]);
});

it('handles zero values correctly', function () {
    $data = [
        'total' => 0,
        'additions' => 0,
        'deletions' => 0,
    ];

    $stats = CommitStatsData::fromArray($data);

    expect($stats->total)->toBe(0);
    expect($stats->additions)->toBe(0);
    expect($stats->deletions)->toBe(0);
});
