<?php

use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Facades\Github;

it('throws type error when passing string instead of Visibility enum', function () {
    expect(fn () => Github::repos()->all(
        per_page: 30,
        page: 2,
        visibility: 'public' // Should be Visibility::PUBLIC
    ))->toThrow(TypeError::class);
});

it('accepts Visibility enum', function () {
    config(['github-client.token' => 'test']);
    expect(Github::repos()->all(per_page: 30, page: 2, visibility: Visibility::PUBLIC))->toBeObject();
});

it('throws validation errors for invalid sort', function () {
    config(['github-client.token' => 'test']);
    expect(fn () => Github::repos()->all(per_page: 30, page: 2, visibility: Visibility::PUBLIC, sort: 'invalid'))
        ->toThrow(InvalidArgumentException::class);
});

it('throws validation errors for invalid direction', function () {
    config(['github-client.token' => 'test']);
    expect(fn () => Github::repos()->all(per_page: 30, page: 2, visibility: Visibility::PUBLIC, sort: 'stars', direction: 'invalid'))
        ->toThrow(InvalidArgumentException::class);
});

it('throws validation errors for invalid per page', function () {
    config(['github-client.token' => 'test']);
    expect(fn () => Github::repos()->all(per_page: 101, page: 2, visibility: Visibility::PUBLIC, sort: 'stars', direction: 'desc'))
        ->toThrow(InvalidArgumentException::class);
});
