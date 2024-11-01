<?php

use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Facades\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);

    // Set up mock responses for all tests
    $mockClient = new MockClient([
        '*' => MockResponse::make([
            [
                'id' => 1,
                'name' => 'test-repo',
                'full_name' => 'test/test-repo',
                'private' => false,
            ]
        ], 200)
    ]);

    Github::connector()->withMockClient($mockClient);
});

it('throws type error when passing string instead of Visibility enum', function () {
    expect(fn () => Github::repos()->all(
        per_page: 30,
        page: 2,
        visibility: 'public' // Should be Visibility::PUBLIC
    ))->toThrow(TypeError::class);
});

it('accepts Visibility enum', function () {
    $response = Github::repos()->all(
        per_page: 30,
        page: 2,
        visibility: Visibility::PUBLIC
    );

    expect($response->json())->toBeArray()
        ->and($response->json())->toHaveKey('0.full_name');
});

it('throws validation errors for invalid sort', function () {
    expect(fn () => Github::repos()->all(
        per_page: 30,
        page: 2,
        visibility: Visibility::PUBLIC,
        sort: 'invalid'
    ))->toThrow(TypeError::class);
});

it('accepts valid Sort enum', function () {
    $response = Github::repos()->all(
        per_page: 30,
        page: 2,
        visibility: Visibility::PUBLIC,
        sort: Sort::CREATED
    );

    expect($response->json())->toBeArray();
});

it('throws type error when passing invalid direction', function () {
    expect(fn () => Github::repos()->all(
        per_page: 30,
        page: 2,
        visibility: Visibility::PUBLIC,
        sort: Sort::CREATED,
        direction: 'invalid'
    ))->toThrow(TypeError::class);
});

it('accepts valid Direction enum', function () {
    $response = Github::repos()->all(
        per_page: 30,
        page: 2,
        visibility: Visibility::PUBLIC,
        sort: Sort::CREATED,
        direction: Direction::DESC
    );

    expect($response->json())->toBeArray();
});

it('throws validation error for invalid per page', function () {
    expect(fn () => Github::repos()->all(
        per_page: 101,
        page: 2,
        visibility: Visibility::PUBLIC,
        sort: Sort::CREATED,
        direction: Direction::DESC
    ))->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 100');
});

it('accepts valid per page value', function () {
    $response = Github::repos()->all(
        per_page: 100,
        page: 2,
        visibility: Visibility::PUBLIC,
        sort: Sort::CREATED,
        direction: Direction::DESC
    );

    expect($response->json())->toBeArray();
});
