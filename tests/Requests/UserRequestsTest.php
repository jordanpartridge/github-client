<?php

use JordanPartridge\GithubClient\Requests\User;
use Saloon\Enums\Method;

describe('User Request', function () {
    describe('User', function () {
        it('constructs without parameters', function () {
            $request = new User();

            expect($request->resolveEndpoint())->toBe('/user');
        });

        it('uses GET method', function () {
            $request = new User();

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('can be instantiated multiple times', function () {
            $request1 = new User();
            $request2 = new User();

            expect($request1)->toBeInstanceOf(User::class);
            expect($request2)->toBeInstanceOf(User::class);
            expect($request1)->not->toBe($request2);
        });

        it('returns consistent endpoint', function () {
            $request = new User();

            expect($request->resolveEndpoint())->toBe('/user');
            expect($request->resolveEndpoint())->toBe('/user');
        });
    });
});
