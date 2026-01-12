<?php

use JordanPartridge\GithubClient\Requests\Commits\Get;
use JordanPartridge\GithubClient\Requests\Commits\Index;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;

describe('Commits Requests', function () {
    describe('Commits\Get', function () {
        it('constructs with valid repo and commit sha', function () {
            $repo = Repo::fromFullName('owner/repo');
            $sha = 'a' . str_repeat('0', 39);
            $request = new Get($repo, $sha);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/commits/' . $sha);
        });

        it('uses GET method', function () {
            $repo = Repo::fromFullName('owner/repo');
            $sha = 'a' . str_repeat('0', 39);
            $request = new Get($repo, $sha);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('validates commit SHA format - must be 40 hex characters', function () {
            $repo = Repo::fromFullName('owner/repo');
            new Get($repo, 'invalid-sha');
        })->throws(InvalidArgumentException::class, 'Invalid commit SHA format');

        it('validates commit SHA format - rejects short SHA', function () {
            $repo = Repo::fromFullName('owner/repo');
            new Get($repo, 'a0b1c2d');
        })->throws(InvalidArgumentException::class, 'Invalid commit SHA format');

        it('validates commit SHA format - rejects non-hex characters', function () {
            $repo = Repo::fromFullName('owner/repo');
            new Get($repo, 'g' . str_repeat('0', 39));
        })->throws(InvalidArgumentException::class, 'Invalid commit SHA format');

        it('accepts valid 40-character hex SHA', function () {
            $repo = Repo::fromFullName('owner/repo');
            $sha = 'abcdef1234567890abcdef1234567890abcdef12';
            $request = new Get($repo, $sha);

            expect($request)->toBeInstanceOf(Get::class);
        });

        it('accepts uppercase hex characters in SHA', function () {
            $repo = Repo::fromFullName('owner/repo');
            $sha = 'ABCDEF1234567890ABCDEF1234567890ABCDEF12';
            $request = new Get($repo, $sha);

            expect($request)->toBeInstanceOf(Get::class);
        });

        it('has createDtoFromResponse method', function () {
            $repo = Repo::fromFullName('owner/repo');
            $sha = 'a' . str_repeat('0', 39);
            $request = new Get($repo, $sha);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Commits\Index', function () {
        it('constructs with repo name', function () {
            $request = new Index('owner/repo');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/commits');
        });

        it('uses GET method', function () {
            $request = new Index('owner/repo');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts pagination parameters', function () {
            $request = new Index('owner/repo', per_page: 50, page: 2);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 50, 'page' => 2]);
        });

        it('filters null values from query parameters', function () {
            $request = new Index('owner/repo', per_page: 30);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 30]);
            expect($query)->not->toHaveKey('page');
        });

        it('throws exception for per_page less than 1', function () {
            new Index('owner/repo', per_page: 0);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('throws exception for per_page greater than 100', function () {
            new Index('owner/repo', per_page: 101);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('accepts per_page at boundaries', function () {
            $request1 = new Index('owner/repo', per_page: 1);
            $request100 = new Index('owner/repo', per_page: 100);

            expect($request1)->toBeInstanceOf(Index::class);
            expect($request100)->toBeInstanceOf(Index::class);
        });

        it('has createDtoFromResponse method', function () {
            $request = new Index('owner/repo');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });
});
