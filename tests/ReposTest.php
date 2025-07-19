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
            $this->createMockRepoData('test-repo', 1, 'test'),
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
        $olderRepo = $this->createMockRepoData('older-repo', 1, 'test');
        $olderRepo['created_at'] = '2024-01-01T00:00:00Z';
        
        $newerRepo = $this->createMockRepoData('newer-repo', 2, 'test');
        $newerRepo['created_at'] = '2024-01-02T00:00:00Z';
        
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$olderRepo, $newerRepo], 200),
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

describe('auto-pagination functionality', function () {
    it('fetches all repositories across multiple pages', function () {
        // Mock multiple pages of responses
        $page1Response = MockResponse::make([
            $this->createMockRepoData('repo-1', 1, 'test'),
            $this->createMockRepoData('repo-2', 2, 'test'),
        ], 200, [
            'Link' => '</user/repos?page=2>; rel="next", </user/repos?page=2>; rel="last"',
        ]);

        $page2Response = MockResponse::make([
            $this->createMockRepoData('repo-3', 3, 'test'),
        ], 200, [
            'Link' => '</user/repos?page=1>; rel="first", </user/repos?page=1>; rel="prev"',
        ]);

        $mockClient = new MockClient();
        $mockClient->addResponse($page1Response);
        $mockClient->addResponse($page2Response);
        
        Github::connector()->withMockClient($mockClient);

        $allRepos = Github::repos()->allWithPagination();

        expect($allRepos)
            ->toBeArray()
            ->toHaveCount(3)
            ->and($allRepos[0]->name)->toBe('repo-1')
            ->and($allRepos[1]->name)->toBe('repo-2')
            ->and($allRepos[2]->name)->toBe('repo-3');
    });

    it('stops pagination when no more pages available', function () {
        // Mock single page response without next link
        $singlePageResponse = MockResponse::make([
            $this->createMockRepoData('only-repo', 1, 'test'),
        ], 200);

        Github::connector()->withMockClient(new MockClient([
            '*' => $singlePageResponse,
        ]));

        $allRepos = Github::repos()->allWithPagination();

        expect($allRepos)
            ->toBeArray()
            ->toHaveCount(1)
            ->and($allRepos[0]->name)->toBe('only-repo');
    });

    it('handles empty repository list', function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([], 200),
        ]));

        $allRepos = Github::repos()->allWithPagination();

        expect($allRepos)
            ->toBeArray()
            ->toHaveCount(0);
    });

    it('respects filtering parameters during pagination', function () {
        $publicReposResponse = MockResponse::make([
            $this->createMockRepoData('public-repo', 1, 'test'),
        ], 200);

        Github::connector()->withMockClient(new MockClient([
            '*' => $publicReposResponse,
        ]));

        $allRepos = Github::repos()->allWithPagination(
            visibility: Visibility::PUBLIC,
            sort: Sort::CREATED,
            direction: Direction::DESC
        );

        expect($allRepos)
            ->toBeArray()
            ->toHaveCount(1)
            ->and($allRepos[0]->name)->toBe('public-repo');
    });
});
