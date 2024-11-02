<?php

use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Facades\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);

    // Set up default mock response
    $mockClient = new MockClient([
        '*' => MockResponse::make([
            [
                'id' => 1,
                'name' => 'test-repo',
                'full_name' => 'test/test-repo',
                'visibility' => 'public',
                'created_at' => '2024-01-01T00:00:00Z',
            ],
        ], 200),
    ]);

    Github::connector()->withMockClient($mockClient);
});

describe('visibility parameter validation', function () {
    it('throws type error when passing string instead of Visibility enum', function () {
        expect(fn () => Github::repos()->all(
            per_page: 30,
            page: 2,
            visibility: 'public'
        ))->toThrow(TypeError::class);
    });

    it('accepts Visibility enum', function () {
        $response = Github::repos()->all(
            per_page: 30,
            page: 2,
            visibility: Visibility::PUBLIC
        );

        $repos = $response->json();
        expect($repos)
            ->toBeArray()
            ->and($repos[0])
            ->toHaveKeys(['id', 'name', 'full_name', 'visibility'])
            ->and($repos[0]['visibility'])->toBe('public');
    });
});

describe('sort parameter validation', function () {
    it('throws type error for invalid sort value', function () {
        expect(fn () => Github::repos()->all(
            per_page: 30,
            page: 2,
            visibility: Visibility::PUBLIC,
            sort: 'invalid'
        ))->toThrow(TypeError::class);
    });

    it('accepts valid Sort enum and orders results correctly', function () {
        // Setup mock with multiple repositories for sorting test
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([
                [
                    'id' => 1,
                    'created_at' => '2024-01-01T00:00:00Z',
                    'name' => 'older-repo',
                ],
                [
                    'id' => 2,
                    'created_at' => '2024-01-02T00:00:00Z',
                    'name' => 'newer-repo',
                ],
            ], 200),
        ]));

        $response = Github::repos()->all(
            per_page: 30,
            page: 2,
            visibility: Visibility::PUBLIC,
            sort: Sort::CREATED
        );

        $repos = $response->json();
        expect($repos)
            ->toBeArray()
            ->toHaveCount(2)
            ->and($repos[0]['created_at'])->toBe('2024-01-01T00:00:00Z')
            ->and($repos[1]['created_at'])->toBe('2024-01-02T00:00:00Z');
    });
});

describe('direction parameter validation', function () {
    it('throws type error for invalid direction value', function () {
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

        expect($response->json())
            ->toBeArray()
            ->toHaveKey('0.created_at');
    });
});

describe('per_page parameter validation', function () {
    it('throws validation error for exceeding maximum per_page', function () {
        expect(fn () => Github::repos()->all(
            per_page: 101,
            page: 2,
            visibility: Visibility::PUBLIC
        ))->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 100');
    });

    it('throws validation error for zero per_page', function () {
        expect(fn () => Github::repos()->all(
            per_page: 0,
            page: 1,
            visibility: Visibility::PUBLIC
        ))->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 100');
    });

    it('throws validation error for negative per_page', function () {
        expect(fn () => Github::repos()->all(
            per_page: -1,
            page: 1,
            visibility: Visibility::PUBLIC
        ))->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 100');
    });

    it('accepts valid per_page value', function () {
        $response = Github::repos()->all(
            per_page: 100,
            page: 2,
            visibility: Visibility::PUBLIC,
            sort: Sort::CREATED,
            direction: Direction::DESC
        );

        expect($response->json())
            ->toBeArray()
            ->and($response->status())->toBe(200);
    });
});
