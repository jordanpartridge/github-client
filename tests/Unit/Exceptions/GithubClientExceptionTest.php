<?php

use JordanPartridge\GithubClient\Exceptions\GithubClientException;

// Create a concrete implementation for testing since GithubClientException is abstract
function createTestException(string $message = '', int $code = 0, ?Throwable $previous = null, array $context = []): GithubClientException
{
    return new class ($message, $code, $previous, $context) extends GithubClientException {};
}

describe('GithubClientException', function () {
    describe('constructor', function () {
        it('sets message correctly', function () {
            $exception = createTestException('Test error message');

            expect($exception->getMessage())->toBe('Test error message');
        });

        it('sets code correctly', function () {
            $exception = createTestException('Test', 500);

            expect($exception->getCode())->toBe(500);
        });

        it('sets previous exception correctly', function () {
            $previous = new Exception('Previous error');
            $exception = createTestException('Test', 0, $previous);

            expect($exception->getPrevious())->toBe($previous);
        });

        it('sets context correctly', function () {
            $context = ['key1' => 'value1', 'key2' => 'value2'];
            $exception = createTestException('Test', 0, null, $context);

            expect($exception->getContext())->toBe($context);
        });

        it('defaults to empty context', function () {
            $exception = createTestException('Test');

            expect($exception->getContext())->toBe([]);
        });
    });

    describe('getContext', function () {
        it('returns the context array', function () {
            $context = ['request_id' => '12345', 'endpoint' => '/repos'];
            $exception = createTestException('Test', 0, null, $context);

            expect($exception->getContext())->toBe($context);
        });
    });

    describe('addContext', function () {
        it('adds a single context value', function () {
            $exception = createTestException('Test');
            $exception->addContext('key', 'value');

            expect($exception->getContext())->toBe(['key' => 'value']);
        });

        it('adds multiple context values', function () {
            $exception = createTestException('Test');
            $exception->addContext('key1', 'value1');
            $exception->addContext('key2', 'value2');

            expect($exception->getContext())->toBe([
                'key1' => 'value1',
                'key2' => 'value2',
            ]);
        });

        it('overwrites existing context key', function () {
            $exception = createTestException('Test', 0, null, ['key' => 'original']);
            $exception->addContext('key', 'updated');

            expect($exception->getContext()['key'])->toBe('updated');
        });

        it('returns $this for method chaining', function () {
            $exception = createTestException('Test');
            $result = $exception->addContext('key', 'value');

            expect($result)->toBe($exception);
        });

        it('allows chaining multiple addContext calls', function () {
            $exception = createTestException('Test');
            $exception
                ->addContext('key1', 'value1')
                ->addContext('key2', 'value2')
                ->addContext('key3', 'value3');

            expect($exception->getContext())->toBe([
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
            ]);
        });

        it('accepts various value types', function () {
            $exception = createTestException('Test');
            $exception->addContext('string', 'text');
            $exception->addContext('int', 42);
            $exception->addContext('float', 3.14);
            $exception->addContext('bool', true);
            $exception->addContext('array', ['a', 'b']);
            $exception->addContext('null', null);

            $context = $exception->getContext();
            expect($context['string'])->toBe('text')
                ->and($context['int'])->toBe(42)
                ->and($context['float'])->toBe(3.14)
                ->and($context['bool'])->toBeTrue()
                ->and($context['array'])->toBe(['a', 'b'])
                ->and($context['null'])->toBeNull();
        });
    });

    describe('inheritance', function () {
        it('extends Exception', function () {
            $exception = createTestException('Test');

            expect($exception)->toBeInstanceOf(Exception::class);
        });

        it('is throwable', function () {
            $exception = createTestException('Test');

            expect($exception)->toBeInstanceOf(Throwable::class);
        });
    });
});
