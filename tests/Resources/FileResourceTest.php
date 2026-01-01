<?php

use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\FileResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);
});

describe('FileResource', function () {
    it('can access files resource through Github facade', function () {
        $resource = Github::files();

        expect($resource)->toBeInstanceOf(FileResource::class);
    });

    describe('all method', function () {
        it('can fetch files for a valid commit', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'sha' => '1234567890123456789012345678901234567890',
                    'url' => 'https://api.github.com/repos/owner/repo/git/commits/1234567890123456789012345678901234567890',
                    'tree' => [
                        'sha' => '0987654321098765432109876543210987654321',
                        'url' => 'https://api.github.com/repos/owner/repo/git/trees/0987654321098765432109876543210987654321',
                    ],
                    'files' => [
                        [
                            'sha' => 'abc123',
                            'filename' => 'src/test.php',
                            'status' => 'modified',
                            'additions' => 10,
                            'deletions' => 5,
                            'changes' => 15,
                        ],
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::files()->all(
                'owner/repo',
                '1234567890123456789012345678901234567890',
            );

            expect($response->status())->toBe(200)
                ->and($response->json())->toBeArray()
                ->and($response->json())->toHaveKey('files');
        });

        it('throws exception for invalid repository name format', function () {
            expect(fn () => Github::files()->all(
                'invalid-repo-name',
                '1234567890123456789012345678901234567890',
            ))->toThrow(InvalidArgumentException::class);
        });

        it('throws exception for invalid commit SHA format', function () {
            expect(fn () => Github::files()->all(
                'owner/repo',
                'invalid-sha',
            ))->toThrow(InvalidArgumentException::class, 'Invalid commit SHA format');
        });

        it('throws exception for commit SHA with invalid characters', function () {
            expect(fn () => Github::files()->all(
                'owner/repo',
                'ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ',
            ))->toThrow(InvalidArgumentException::class, 'Invalid commit SHA format');
        });

        it('throws exception for commit SHA that is too short', function () {
            expect(fn () => Github::files()->all(
                'owner/repo',
                '1234567890',
            ))->toThrow(InvalidArgumentException::class, 'Invalid commit SHA format');
        });

        it('throws exception for commit SHA that is too long', function () {
            expect(fn () => Github::files()->all(
                'owner/repo',
                '12345678901234567890123456789012345678901234567890',
            ))->toThrow(InvalidArgumentException::class, 'Invalid commit SHA format');
        });

        it('accepts valid 40-character lowercase hex SHA', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['files' => []], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::files()->all(
                'owner/repo',
                'abcdef1234567890abcdef1234567890abcdef12',
            );

            expect($response->status())->toBe(200);
        });

        it('accepts valid 40-character uppercase hex SHA', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['files' => []], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::files()->all(
                'owner/repo',
                'ABCDEF1234567890ABCDEF1234567890ABCDEF12',
            );

            expect($response->status())->toBe(200);
        });

        it('accepts valid mixed-case 40-character hex SHA', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['files' => []], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::files()->all(
                'owner/repo',
                'AbCdEf1234567890AbCdEf1234567890AbCdEf12',
            );

            expect($response->status())->toBe(200);
        });

        it('uses Repo value object internally', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['files' => []], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            // This test verifies the internal use of Repo::fromFullName
            $response = Github::files()->all(
                'jordanpartridge/github-client',
                '1234567890123456789012345678901234567890',
            );

            expect($response->status())->toBe(200);
        });
    });
});
