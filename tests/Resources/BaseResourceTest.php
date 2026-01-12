<?php

use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Github as GithubClient;
use JordanPartridge\GithubClient\Resources\BaseResource;
use JordanPartridge\GithubClient\Resources\RepoResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);
});

describe('BaseResource', function () {
    it('provides access to the Github instance', function () {
        $mockClient = new MockClient([
            '*' => MockResponse::make([], 200),
        ]);

        Github::connector()->withMockClient($mockClient);

        $resource = Github::repos();

        expect($resource)->toBeInstanceOf(RepoResource::class)
            ->and($resource->github())->toBeInstanceOf(GithubClient::class);
    });

    it('provides access to the connector via convenience method', function () {
        $mockClient = new MockClient([
            '*' => MockResponse::make([], 200),
        ]);

        Github::connector()->withMockClient($mockClient);

        $resource = Github::repos();

        expect($resource->connector())->not->toBeNull();
    });

    it('is readonly', function () {
        $reflection = new ReflectionClass(BaseResource::class);

        expect($reflection->isReadOnly())->toBeTrue();
    });

    it('implements ResourceInterface', function () {
        $reflection = new ReflectionClass(BaseResource::class);
        $interfaces = $reflection->getInterfaces();

        expect(array_keys($interfaces))
            ->toContain('JordanPartridge\GithubClient\Contracts\ResourceInterface');
    });

    it('is abstract', function () {
        $reflection = new ReflectionClass(BaseResource::class);

        expect($reflection->isAbstract())->toBeTrue();
    });

    it('has private github property', function () {
        $reflection = new ReflectionClass(BaseResource::class);
        $property = $reflection->getProperty('github');

        expect($property->isPrivate())->toBeTrue();
    });
});
