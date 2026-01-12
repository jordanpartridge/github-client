<?php

use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Data\Repos\SearchRepositoriesData;

it('can create SearchRepositoriesData from array', function () {
    $data = [
        'total_count' => 100,
        'incomplete_results' => false,
        'items' => [
            $this->createMockRepoData('repo1', 1, 'owner1'),
            $this->createMockRepoData('repo2', 2, 'owner2'),
        ],
    ];

    $searchResults = SearchRepositoriesData::fromArray($data);

    expect($searchResults->total_count)->toBe(100);
    expect($searchResults->incomplete_results)->toBeFalse();
    expect($searchResults->items)->toHaveCount(2);
    expect($searchResults->items[0])->toBeInstanceOf(RepoData::class);
    expect($searchResults->items[0]->name)->toBe('repo1');
    expect($searchResults->items[1])->toBeInstanceOf(RepoData::class);
    expect($searchResults->items[1]->name)->toBe('repo2');
});

it('can convert SearchRepositoriesData to array', function () {
    $data = [
        'total_count' => 50,
        'incomplete_results' => true,
        'items' => [
            $this->createMockRepoData('test-repo', 1, 'test-owner'),
        ],
    ];

    $searchResults = SearchRepositoriesData::fromArray($data);
    $array = $searchResults->toArray();

    expect($array['total_count'])->toBe(50);
    expect($array['incomplete_results'])->toBeTrue();
    expect($array['items'])->toHaveCount(1);
    expect($array['items'][0]['name'])->toBe('test-repo');
});

it('handles empty items array', function () {
    $data = [
        'total_count' => 0,
        'incomplete_results' => false,
        'items' => [],
    ];

    $searchResults = SearchRepositoriesData::fromArray($data);

    expect($searchResults->total_count)->toBe(0);
    expect($searchResults->incomplete_results)->toBeFalse();
    expect($searchResults->items)->toBe([]);
});

it('handles missing items key', function () {
    $data = [
        'total_count' => 0,
        'incomplete_results' => false,
    ];

    $searchResults = SearchRepositoriesData::fromArray($data);

    expect($searchResults->items)->toBe([]);
});

it('handles incomplete results flag', function () {
    $data = [
        'total_count' => 1000,
        'incomplete_results' => true,
        'items' => [],
    ];

    $searchResults = SearchRepositoriesData::fromArray($data);

    expect($searchResults->incomplete_results)->toBeTrue();
});

it('handles large result sets', function () {
    $items = [];
    for ($i = 1; $i <= 30; $i++) {
        $items[] = $this->createMockRepoData("repo{$i}", $i, 'owner');
    }

    $data = [
        'total_count' => 1500,
        'incomplete_results' => false,
        'items' => $items,
    ];

    $searchResults = SearchRepositoriesData::fromArray($data);

    expect($searchResults->total_count)->toBe(1500);
    expect($searchResults->items)->toHaveCount(30);
});
