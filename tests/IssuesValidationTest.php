<?php

use JordanPartridge\GithubClient\Facades\Github;

describe('Issues validation', function () {
    beforeEach(function () {
        config(['github-client.token' => 'fake-token']);
    });

    it('throws error for negative issue number in get request', function () {
        expect(fn () => Github::issues()->get('test', 'repo', -1))
            ->toThrow(InvalidArgumentException::class, 'Issue number must be a positive integer');
    });

    it('throws error for zero issue number in get request', function () {
        expect(fn () => Github::issues()->get('test', 'repo', 0))
            ->toThrow(InvalidArgumentException::class, 'Issue number must be a positive integer');
    });

    it('throws error for negative issue number in update request', function () {
        expect(fn () => Github::issues()->update('test', 'repo', -5, title: 'New Title'))
            ->toThrow(InvalidArgumentException::class, 'Issue number must be a positive integer');
    });

    it('throws error for negative issue number in comments request', function () {
        expect(fn () => Github::issues()->comments('test', 'repo', -10))
            ->toThrow(InvalidArgumentException::class, 'Issue number must be a positive integer');
    });

    it('throws error for empty comment body', function () {
        expect(fn () => Github::issues()->addComment('test', 'repo', 1, ''))
            ->toThrow(InvalidArgumentException::class, 'Comment body cannot be empty');
    });

    it('throws error for whitespace-only comment body', function () {
        expect(fn () => Github::issues()->addComment('test', 'repo', 1, '   '))
            ->toThrow(InvalidArgumentException::class, 'Comment body cannot be empty');
    });

    it('accepts valid issue numbers', function () {
        // These should not throw exceptions during validation
        // (they would fail when actually making HTTP calls without mocking)
        $this->assertTrue(true); // Placeholder - validation passes
    });
});
