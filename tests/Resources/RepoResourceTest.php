<?php

use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Data\Repos\SearchRepositoriesData;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Repos\Type;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\RepoResource;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);
});

describe('RepoResource', function () {
    it('can access repos resource through Github facade', function () {
        $resource = Github::repos();

        expect($resource)->toBeInstanceOf(RepoResource::class);
    });

    describe('all method', function () {
        it('returns a Response object', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::repos()->all();

            expect($response->status())->toBe(200);
        });

        it('accepts all optional parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::repos()->all(
                per_page: 50,
                page: 2,
                visibility: Visibility::PUBLIC,
                sort: Sort::CREATED,
                direction: Direction::DESC,
                type: Type::Owner,
            );

            expect($response->status())->toBe(200);
        });

        it('works with null parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::repos()->all(
                per_page: null,
                page: null,
                visibility: null,
                sort: null,
                direction: null,
                type: null,
            );

            expect($response->status())->toBe(200);
        });
    });

    describe('allWithPagination method', function () {
        it('returns array of RepoData', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $repos = Github::repos()->allWithPagination();

            expect($repos)->toBeArray()
                ->and($repos[0])->toBeInstanceOf(RepoData::class);
        });

        it('accepts all optional parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $repos = Github::repos()->allWithPagination(
                per_page: 50,
                visibility: Visibility::PRIVATE,
                sort: Sort::UPDATED,
                direction: Direction::ASC,
                type: Type::Member,
            );

            expect($repos)->toBeArray();
        });

        it('handles empty repository list', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $repos = Github::repos()->allWithPagination();

            expect($repos)
                ->toBeArray()
                ->toBeEmpty();
        });
    });

    describe('get method', function () {
        it('returns RepoData for valid repository', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make($this->createMockRepoData('test-repo', 1, 'owner'), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $repo = Repo::fromFullName('owner/test-repo');
            $result = Github::repos()->get($repo);

            expect($result)
                ->toBeInstanceOf(RepoData::class)
                ->and($result->name)->toBe('test-repo');
        });

        it('accepts Repo value object as parameter', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make($this->createMockRepoData(), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $repo = Repo::fromFullName('jordanpartridge/github-client');
            $result = Github::repos()->get($repo);

            expect($result)->toBeInstanceOf(RepoData::class);
        });
    });

    describe('delete method', function () {
        it('returns Response for delete operation', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 204),
            ]);

            Github::connector()->withMockClient($mockClient);

            $repo = Repo::fromFullName('owner/test-repo');
            $response = Github::repos()->delete($repo);

            expect($response->status())->toBe(204);
        });

        it('accepts Repo value object as parameter', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 204),
            ]);

            Github::connector()->withMockClient($mockClient);

            $repo = Repo::fromFullName('jordanpartridge/github-client');
            $response = Github::repos()->delete($repo);

            expect($response->status())->toBe(204);
        });
    });

    describe('search method', function () {
        it('returns SearchRepositoriesData', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'total_count' => 1,
                    'incomplete_results' => false,
                    'items' => [$this->createMockRepoData()],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::repos()->search('laravel');

            expect($result)
                ->toBeInstanceOf(SearchRepositoriesData::class)
                ->and($result->total_count)->toBe(1);
        });

        it('accepts all optional parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'total_count' => 0,
                    'incomplete_results' => false,
                    'items' => [],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::repos()->search(
                query: 'topic:conduit-component',
                sort: 'stars',
                order: Direction::DESC,
                per_page: 20,
                page: 1,
            );

            expect($result)->toBeInstanceOf(SearchRepositoriesData::class);
        });

        it('can search by topic', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'total_count' => 5,
                    'incomplete_results' => false,
                    'items' => [
                        $this->createMockRepoData('repo-1'),
                        $this->createMockRepoData('repo-2'),
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::repos()->search('topic:laravel');

            expect($result->total_count)->toBe(5)
                ->and($result->items)->toHaveCount(2);
        });

        it('can search with sort parameter', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'total_count' => 0,
                    'incomplete_results' => false,
                    'items' => [],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::repos()->search('php', sort: 'stars');

            expect($result)->toBeInstanceOf(SearchRepositoriesData::class);
        });

        it('handles empty search results', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'total_count' => 0,
                    'incomplete_results' => false,
                    'items' => [],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::repos()->search('nonexistent-query-12345');

            expect($result->total_count)->toBe(0)
                ->and($result->items)->toBeEmpty();
        });
    });

    describe('type parameter', function () {
        it('can filter by All type', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::repos()->all(type: Type::All);

            expect($response->status())->toBe(200);
        });

        it('can filter by Public type', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::repos()->all(type: Type::Public);

            expect($response->status())->toBe(200);
        });

        it('can filter by Private type', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::repos()->all(type: Type::Private);

            expect($response->status())->toBe(200);
        });

        it('can filter by Forks type', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::repos()->all(type: Type::Forks);

            expect($response->status())->toBe(200);
        });

        it('can filter by Sources type', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockRepoData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::repos()->all(type: Type::Sources);

            expect($response->status())->toBe(200);
        });
    });
});
