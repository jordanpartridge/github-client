<?php

use JordanPartridge\GithubClient\GithubConnector;
use Saloon\Http\Connector;

it('has the correct base url', function () {
    expect(new GithubConnector('token'))
        ->toBeInstanceOf(GithubConnector::class)
        ->resolveBaseUrl()
        ->toBe('https://api.github.com');
});

arch('extends saloon connector')
    ->expect(GithubConnector::class)
    ->toExtend(Connector::class);


