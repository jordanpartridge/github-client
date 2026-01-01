<?php

use JordanPartridge\GithubClient\Requests\Files\Index;
use Saloon\Enums\Method;

describe('Files Requests', function () {
    describe('Files\Index', function () {
        it('constructs with valid repo name and commit sha', function () {
            $sha = 'a' . str_repeat('0', 39);
            $request = new Index('owner/repo', $sha);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/commits/' . $sha . '/files');
        });

        it('uses GET method', function () {
            $sha = 'a' . str_repeat('0', 39);
            $request = new Index('owner/repo', $sha);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('validates repo name format', function () {
            $sha = 'a' . str_repeat('0', 39);
            new Index('invalid-repo-name', $sha);
        })->throws(InvalidArgumentException::class);

        it('validates commit SHA format - must be 40 hex characters', function () {
            new Index('owner/repo', 'invalid-sha');
        })->throws(InvalidArgumentException::class, 'Invalid commit SHA format');

        it('validates commit SHA format - rejects short SHA', function () {
            new Index('owner/repo', 'a0b1c2d');
        })->throws(InvalidArgumentException::class, 'Invalid commit SHA format');

        it('validates commit SHA format - rejects non-hex characters', function () {
            new Index('owner/repo', 'g' . str_repeat('0', 39));
        })->throws(InvalidArgumentException::class, 'Invalid commit SHA format');

        it('accepts valid 40-character hex SHA', function () {
            $sha = 'abcdef1234567890abcdef1234567890abcdef12';
            $request = new Index('owner/repo', $sha);

            expect($request)->toBeInstanceOf(Index::class);
        });

        it('accepts uppercase hex characters in SHA', function () {
            $sha = 'ABCDEF1234567890ABCDEF1234567890ABCDEF12';
            $request = new Index('owner/repo', $sha);

            expect($request)->toBeInstanceOf(Index::class);
        });

        it('constructs endpoint correctly with different repo names', function () {
            $sha = 'a' . str_repeat('0', 39);
            $request = new Index('my-org/my-project', $sha);

            expect($request->resolveEndpoint())->toBe('repos/my-org/my-project/commits/' . $sha . '/files');
        });
    });
});
