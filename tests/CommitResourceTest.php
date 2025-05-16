<?php

use Carbon\Carbon;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);

    // Set up mock client for basic requests
    $mockClient = new MockClient([
        '*' => MockResponse::make([], 200),
    ]);

    Github::connector()->withMockClient($mockClient);
    $this->resource = new CommitResource(Github::connector());
});

it('can fetch all commits for a repository', function () {
    $response = $this->resource->all('jordanpartridge/github-client');

    expect($response)->toBeArray();
});

it('can fetch a specific commit by SHA', function () {
    // Use a valid SHA format for testing (40 characters hex)
    $sha = '1234567890123456789012345678901234567890';

    // Generate carbon date
    $date = Carbon::now()->toIso8601String();

    // Set up a more complete mock response for the commit
    Github::connector()->withMockClient(new MockClient([
        '*' => MockResponse::make([
            'sha' => $sha,
            'node_id' => 'MDQ6QmxvYjE0ODMzNDY0NjpkZjE5N2EzZTgwMjhmN2E5ODM3MDc2M2ZlN2EzNWFlYjYzOTMxOGExOg==',
            'url' => 'https://api.github.com/repos/jordanpartridge/github-client/git/commits/'.$sha,
            'html_url' => 'https://github.com/jordanpartridge/github-client/commit/'.$sha,
            'comments_url' => 'https://api.github.com/repos/jordanpartridge/github-client/commits/'.$sha.'/comments',
            'commit' => [
                'author' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'date' => $date,
                ],
                'committer' => [
                    'name' => 'GitHub',
                    'email' => 'noreply@github.com',
                    'date' => $date,
                ],
                'message' => 'Test commit message',
                'tree' => [
                    'sha' => '1234567890123456789012345678901234567890',
                    'url' => 'https://api.github.com/repos/jordanpartridge/github-client/git/trees/1234567890123456789012345678901234567890',
                ],
                'url' => 'https://api.github.com/repos/jordanpartridge/github-client/git/commits/'.$sha,
                'comment_count' => 0,
                'verification' => [
                    'verified' => true,
                    'reason' => 'valid',
                    'signature' => 'signature',
                    'payload' => 'payload',
                ],
            ],
            'author' => null,
            'committer' => null,
            'parents' => [],
            'stats' => [
                'additions' => 10,
                'deletions' => 5,
                'total' => 15,
            ],
            'files' => [],
        ], 200),
    ]));

    $response = $this->resource->get('jordanpartridge/github-client', $sha);

    // Test passes if no exception is thrown
    expect($response->sha)->toBe($sha);
});

it('validates repository name format', function () {
    expect(fn () => $this->resource->all('invalid-repo-name'))
        ->toThrow(InvalidArgumentException::class);
});

it('handles pagination parameters correctly', function () {
    $response = $this->resource->all('jordanpartridge/github-client', 50, 2);

    // Test passes if no exception is thrown
    expect($response)->toBeArray();
});

it('converts repository name to value object correctly', function () {
    $repo = Repo::fromFullName('jordanpartridge/github-client');

    expect($repo->owner())->toBe('jordanpartridge')
        ->and($repo->name())->toBe('github-client');
});
