<?php

use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Tests\TestCase;
use JordanPartridge\GithubClient\ValueObjects\Repo;

uses(TestCase::class);

beforeEach(function () {
    $this->resource = new CommitResource($this->connector);
});

it('can fetch all commits for a repository', function () {
    $response = $this->resource->all('jordanpartridge/github-client');

    expect($response)
        ->toBeArray()
        ->and($this->connector)
        ->toHaveBeenSentRequest('GET', '/repos/jordanpartridge/github-client/commits')
        ->withQuery(['per_page' => 100, 'page' => 1]);
});

it('can fetch a specific commit by SHA', function () {
    $sha = '123abc';
    $response = $this->resource->get('jordanpartridge/github-client', $sha);

    expect($this->connector)
        ->toHaveBeenSentRequest('GET', "/repos/jordanpartridge/github-client/commits/{$sha}");
});

it('validates repository name format', function () {
    expect(fn () => $this->resource->all('invalid-repo-name'))
        ->toThrow(InvalidArgumentException::class);
});

it('handles pagination parameters correctly', function () {
    $response = $this->resource->all('jordanpartridge/github-client', 50, 2);

    expect($this->connector)
        ->toHaveBeenSentRequest('GET', '/repos/jordanpartridge/github-client/commits')
        ->withQuery(['per_page' => 50, 'page' => 2]);
});

it('converts repository name to value object correctly', function () {
    $repo = Repo::fromFullName('jordanpartridge/github-client');

    expect($repo->owner)->toBe('jordanpartridge')
        ->and($repo->name)->toBe('github-client');
});
