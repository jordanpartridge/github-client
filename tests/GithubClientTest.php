<?php

use JordanPartridge\GithubClient\GithubConnector;
use JordanPartridge\GithubClient\Requests\User;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('has the correct base url', function () {
    expect(new GithubConnector('token'))
        ->toBeInstanceOf(GithubConnector::class)
        ->resolveBaseUrl()
        ->toBe('https://api.github.com');
});

arch('extends saloon connector')
    ->expect(GithubConnector::class)
    ->toExtend(Connector::class);

it('instantiates GithubConnector with the correct token', function () {
    $token = 'test_token';

    // Set the configuration value for testing
    config()->set('github-client.token', $token);

    $connector = new GithubConnector(config('github-client.token'));

    expect($connector)
        ->toBeInstanceOf(GithubConnector::class)
        ->and($connector->getAuthenticator())
        ->toBeInstanceOf(TokenAuthenticator::class);
});

it('fetches the authenticated user', function () {
    $token = 'test_token';

    $mockClient = new MockClient([
        User::class => MockResponse::make(['login' => 'testuser']),
    ]);

    $connector = new GithubConnector($token);
    $connector->withMockClient($mockClient);

    $response = $connector->send(new User);

    expect($response->successful())->toBeTrue()
        ->and($response->json('login'))->toBe('testuser');
});
