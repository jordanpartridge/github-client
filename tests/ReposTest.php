<?php

use JordanPartridge\GithubClient\Facades\Github;

it('throws validation errors for invalid visibility', function () {
    config(['github-client.token' => 'test']);

    expect(fn () => Github::repos(30, 2, 'invalid'))
        ->toThrow(InvalidArgumentException::class);
});

it('throws validation errors for invalid sort', function () {
    config(['github-client.token' => 'test']);
    expect(fn () => Github::repos(30, 2, 'public', 'invalid'))
        ->toThrow(InvalidArgumentException::class);
});

it('throws validation errors for invalid direction', function () {
    config(['github-client.token' => 'test']);
    expect(fn () => Github::repos(30, 2, 'public', 'stars', 'invalid'))
        ->toThrow(InvalidArgumentException::class);
});
