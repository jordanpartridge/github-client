<?php

use JordanPartridge\GithubClient\Exceptions\NetworkException;
use JordanPartridge\GithubClient\Exceptions\GithubClientException;

describe('NetworkException', function () {
    describe('constructor', function () {
        it('sets operation correctly', function () {
            $exception = new NetworkException('fetch repositories', 'Connection refused');

            expect($exception->getOperation())->toBe('fetch repositories');
        });

        it('combines operation and message in full message', function () {
            $exception = new NetworkException('fetch repositories', 'Connection refused');

            expect($exception->getMessage())->toBe('Network error during fetch repositories: Connection refused');
        });

        it('defaults to code 0', function () {
            $exception = new NetworkException('test', 'error');

            expect($exception->getCode())->toBe(0);
        });

        it('accepts custom code', function () {
            $exception = new NetworkException('test', 'error', 500);

            expect($exception->getCode())->toBe(500);
        });

        it('accepts previous exception', function () {
            $previous = new Exception('Original error');
            $exception = new NetworkException('test', 'error', 0, $previous);

            expect($exception->getPrevious())->toBe($previous);
        });

        it('includes operation and original message in context', function () {
            $exception = new NetworkException('fetch pull requests', 'DNS lookup failed');

            $context = $exception->getContext();
            expect($context)->toHaveKey('operation')
                ->and($context)->toHaveKey('original_message')
                ->and($context['operation'])->toBe('fetch pull requests')
                ->and($context['original_message'])->toBe('DNS lookup failed');
        });
    });

    describe('getOperation', function () {
        it('returns the operation name', function () {
            $exception = new NetworkException('create issue', 'Timeout');

            expect($exception->getOperation())->toBe('create issue');
        });
    });

    describe('timeout', function () {
        it('creates timeout exception with formatted message', function () {
            $exception = NetworkException::timeout('API call', 30);

            expect($exception->getMessage())->toBe('Network error during API call: Request timed out after 30 seconds');
        });

        it('sets 408 status code for timeout', function () {
            $exception = NetworkException::timeout('test', 10);

            expect($exception->getCode())->toBe(408);
        });

        it('includes operation in context', function () {
            $exception = NetworkException::timeout('fetch user', 60);

            expect($exception->getOperation())->toBe('fetch user');
        });

        it('handles various timeout values', function () {
            $exception1 = NetworkException::timeout('test', 1);
            $exception2 = NetworkException::timeout('test', 120);

            expect($exception1->getMessage())->toContain('1 seconds')
                ->and($exception2->getMessage())->toContain('120 seconds');
        });
    });

    describe('connectionFailed', function () {
        it('creates connection failed exception without reason', function () {
            $exception = NetworkException::connectionFailed('GitHub API');

            expect($exception->getMessage())->toBe('Network error during GitHub API: Connection failed');
        });

        it('creates connection failed exception with reason', function () {
            $exception = NetworkException::connectionFailed('GitHub API', 'SSL certificate expired');

            expect($exception->getMessage())->toBe('Network error during GitHub API: Connection failed: SSL certificate expired');
        });

        it('sets 503 status code', function () {
            $exception = NetworkException::connectionFailed('test');

            expect($exception->getCode())->toBe(503);
        });

        it('includes operation in context', function () {
            $exception = NetworkException::connectionFailed('update repo');

            expect($exception->getOperation())->toBe('update repo');
        });
    });

    describe('inheritance', function () {
        it('extends GithubClientException', function () {
            $exception = new NetworkException('test', 'error');

            expect($exception)->toBeInstanceOf(GithubClientException::class);
        });

        it('inherits addContext functionality', function () {
            $exception = new NetworkException('test', 'error');
            $exception->addContext('retry_count', 3);

            $context = $exception->getContext();
            expect($context)->toHaveKey('retry_count')
                ->and($context['retry_count'])->toBe(3);
        });
    });
});
