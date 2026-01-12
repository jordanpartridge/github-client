<?php

use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Repos\Type;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Requests\Repos\Delete;
use JordanPartridge\GithubClient\Requests\Repos\Get;
use JordanPartridge\GithubClient\Requests\Repos\Index;
use JordanPartridge\GithubClient\Requests\Repos\Search;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;

describe('Repos Requests', function () {
    describe('Repos\Index', function () {
        it('constructs with default parameters', function () {
            $request = new Index();

            expect($request->resolveEndpoint())->toBe('/user/repos');
        });

        it('uses GET method', function () {
            $request = new Index();

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts all optional parameters', function () {
            $request = new Index(
                per_page: 50,
                page: 2,
                visibility: Visibility::PUBLIC,
                sort: Sort::CREATED,
                direction: Direction::DESC,
                type: Type::Owner,
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe([
                'per_page' => 50,
                'page' => 2,
                'visibility' => 'public',
                'sort' => 'created',
                'direction' => 'desc',
                'type' => 'owner',
            ]);
        });

        it('filters null values from query parameters', function () {
            $request = new Index(per_page: 30, visibility: Visibility::PRIVATE);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 30, 'visibility' => 'private']);
        });

        it('throws exception for per_page less than 1', function () {
            new Index(per_page: 0);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('throws exception for per_page greater than 100', function () {
            new Index(per_page: 101);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('accepts per_page at boundaries', function () {
            $request1 = new Index(per_page: 1);
            $request100 = new Index(per_page: 100);

            expect($request1)->toBeInstanceOf(Index::class);
            expect($request100)->toBeInstanceOf(Index::class);
        });

        it('supports all visibility options', function () {
            $visibilities = [Visibility::PUBLIC, Visibility::PRIVATE, Visibility::INTERNAL];

            foreach ($visibilities as $visibility) {
                $request = new Index(visibility: $visibility);
                expect($request)->toBeInstanceOf(Index::class);
            }
        });

        it('supports all type options', function () {
            $types = [Type::All, Type::Owner, Type::Public, Type::Private, Type::Member, Type::Forks, Type::Sources];

            foreach ($types as $type) {
                $request = new Index(type: $type);
                expect($request)->toBeInstanceOf(Index::class);
            }
        });

        it('has createDtoFromResponse method', function () {
            $request = new Index();

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Repos\Get', function () {
        it('constructs with repo value object', function () {
            $repo = Repo::fromFullName('owner/repo');
            $request = new Get($repo);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo');
        });

        it('uses GET method', function () {
            $repo = Repo::fromFullName('owner/repo');
            $request = new Get($repo);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('constructs endpoint correctly with different repos', function () {
            $repo1 = Repo::fromFullName('org1/project1');
            $repo2 = Repo::fromFullName('org2/project2');

            $request1 = new Get($repo1);
            $request2 = new Get($repo2);

            expect($request1->resolveEndpoint())->toBe('/repos/org1/project1');
            expect($request2->resolveEndpoint())->toBe('/repos/org2/project2');
        });

        it('has createDtoFromResponse method', function () {
            $repo = Repo::fromFullName('owner/repo');
            $request = new Get($repo);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Repos\Delete', function () {
        it('constructs with repo value object', function () {
            $repo = Repo::fromFullName('owner/repo');
            $request = new Delete($repo);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo');
        });

        it('uses DELETE method', function () {
            $repo = Repo::fromFullName('owner/repo');
            $request = new Delete($repo);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::DELETE);
        });

        it('constructs endpoint correctly with different repos', function () {
            $repo1 = Repo::fromFullName('org1/project1');
            $repo2 = Repo::fromFullName('org2/project2');

            $request1 = new Delete($repo1);
            $request2 = new Delete($repo2);

            expect($request1->resolveEndpoint())->toBe('/repos/org1/project1');
            expect($request2->resolveEndpoint())->toBe('/repos/org2/project2');
        });
    });

    describe('Repos\Search', function () {
        it('constructs with required parameters', function () {
            $request = new Search('laravel');

            expect($request->resolveEndpoint())->toBe('/search/repositories');
        });

        it('uses GET method', function () {
            $request = new Search('test');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts all optional parameters', function () {
            $request = new Search(
                'laravel',
                sort: 'stars',
                order: Direction::DESC,
                per_page: 50,
                page: 2,
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe([
                'q' => 'laravel',
                'sort' => 'stars',
                'order' => 'desc',
                'per_page' => 50,
                'page' => 2,
            ]);
        });

        it('filters null values from query parameters', function () {
            $request = new Search('test', per_page: 30);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['q' => 'test', 'per_page' => 30]);
        });

        it('throws exception for per_page less than 1', function () {
            new Search('test', per_page: 0);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('throws exception for per_page greater than 100', function () {
            new Search('test', per_page: 101);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('throws exception for invalid sort value', function () {
            new Search('test', sort: 'invalid');
        })->throws(InvalidArgumentException::class, 'Sort must be one of: stars, forks, help-wanted-issues, updated');

        it('accepts valid sort values', function () {
            $validSorts = ['stars', 'forks', 'help-wanted-issues', 'updated'];

            foreach ($validSorts as $sort) {
                $request = new Search('test', sort: $sort);
                expect($request)->toBeInstanceOf(Search::class);
            }
        });

        it('accepts both order directions', function () {
            $request1 = new Search('test', order: Direction::ASC);
            $request2 = new Search('test', order: Direction::DESC);

            expect($request1)->toBeInstanceOf(Search::class);
            expect($request2)->toBeInstanceOf(Search::class);
        });

        it('has createDtoFromResponse method', function () {
            $request = new Search('test');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });
});
