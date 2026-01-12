<?php

use JordanPartridge\GithubClient\Requests\RateLimit\Get;
use Saloon\Enums\Method;

describe('RateLimit Requests', function () {
    describe('RateLimit\Get', function () {
        it('constructs without parameters', function () {
            $request = new Get();

            expect($request->resolveEndpoint())->toBe('/rate_limit');
        });

        it('uses GET method', function () {
            $request = new Get();

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('has createDtoFromResponse method', function () {
            $request = new Get();

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });

        it('can be instantiated multiple times', function () {
            $request1 = new Get();
            $request2 = new Get();

            expect($request1)->toBeInstanceOf(Get::class);
            expect($request2)->toBeInstanceOf(Get::class);
            expect($request1)->not->toBe($request2);
        });
    });
});
