<?php

use JordanPartridge\GithubClient\Requests\Releases\Get;
use JordanPartridge\GithubClient\Requests\Releases\Index;
use JordanPartridge\GithubClient\Requests\Releases\Latest;
use Saloon\Enums\Method;

describe('Releases Requests', function () {
    describe('Releases\Index', function () {
        it('constructs with required parameters', function () {
            $request = new Index('owner', 'repo');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/releases');
        });

        it('uses GET method', function () {
            $request = new Index('owner', 'repo');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts pagination parameters', function () {
            $request = new Index('owner', 'repo', per_page: 50, page: 2);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 50, 'page' => 2]);
        });

        it('filters null values from query parameters', function () {
            $request = new Index('owner', 'repo', per_page: 30);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 30]);
            expect($query)->not->toHaveKey('page');
        });

        it('throws exception for per_page less than 1', function () {
            new Index('owner', 'repo', per_page: 0);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('throws exception for per_page greater than 100', function () {
            new Index('owner', 'repo', per_page: 101);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('accepts per_page at boundaries', function () {
            $request1 = new Index('owner', 'repo', per_page: 1);
            $request100 = new Index('owner', 'repo', per_page: 100);

            expect($request1)->toBeInstanceOf(Index::class);
            expect($request100)->toBeInstanceOf(Index::class);
        });

        it('has createDtoFromResponse method', function () {
            $request = new Index('owner', 'repo');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Releases\Get', function () {
        it('constructs with required parameters', function () {
            $request = new Get('owner', 'repo', 12345);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/releases/12345');
        });

        it('uses GET method', function () {
            $request = new Get('owner', 'repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('constructs endpoint with different release IDs', function () {
            $request1 = new Get('owner', 'repo', 1);
            $request2 = new Get('owner', 'repo', 999999);

            expect($request1->resolveEndpoint())->toBe('/repos/owner/repo/releases/1');
            expect($request2->resolveEndpoint())->toBe('/repos/owner/repo/releases/999999');
        });

        it('has createDtoFromResponse method', function () {
            $request = new Get('owner', 'repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Releases\Latest', function () {
        it('constructs with required parameters', function () {
            $request = new Latest('owner', 'repo');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/releases/latest');
        });

        it('uses GET method', function () {
            $request = new Latest('owner', 'repo');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('constructs endpoint with different repos', function () {
            $request1 = new Latest('org1', 'project1');
            $request2 = new Latest('org2', 'project2');

            expect($request1->resolveEndpoint())->toBe('/repos/org1/project1/releases/latest');
            expect($request2->resolveEndpoint())->toBe('/repos/org2/project2/releases/latest');
        });

        it('has createDtoFromResponse method', function () {
            $request = new Latest('owner', 'repo');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });
});
