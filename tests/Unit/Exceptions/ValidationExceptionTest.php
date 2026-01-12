<?php

use JordanPartridge\GithubClient\Exceptions\ValidationException;
use JordanPartridge\GithubClient\Exceptions\GithubClientException;

describe('ValidationException', function () {
    describe('constructor', function () {
        it('sets field correctly', function () {
            $exception = new ValidationException('per_page', 200, 'Must be between 1 and 100');

            expect($exception->getField())->toBe('per_page');
        });

        it('sets value correctly', function () {
            $exception = new ValidationException('per_page', 200, 'Must be between 1 and 100');

            expect($exception->getValue())->toBe(200);
        });

        it('combines field and message in full message', function () {
            $exception = new ValidationException('per_page', 200, 'Must be between 1 and 100');

            expect($exception->getMessage())->toBe("Validation failed for field 'per_page': Must be between 1 and 100");
        });

        it('defaults to 422 status code', function () {
            $exception = new ValidationException('field', 'value', 'error');

            expect($exception->getCode())->toBe(422);
        });

        it('accepts custom status code', function () {
            $exception = new ValidationException('field', 'value', 'error', 400);

            expect($exception->getCode())->toBe(400);
        });

        it('accepts previous exception', function () {
            $previous = new Exception('Original');
            $exception = new ValidationException('field', 'value', 'error', 422, $previous);

            expect($exception->getPrevious())->toBe($previous);
        });

        it('includes field, value, and validation message in context', function () {
            $exception = new ValidationException('issue_number', -5, 'Must be a positive integer');

            $context = $exception->getContext();
            expect($context)->toHaveKey('field')
                ->and($context)->toHaveKey('value')
                ->and($context)->toHaveKey('validation_message')
                ->and($context['field'])->toBe('issue_number')
                ->and($context['value'])->toBe(-5)
                ->and($context['validation_message'])->toBe('Must be a positive integer');
        });
    });

    describe('getField', function () {
        it('returns the field name', function () {
            $exception = new ValidationException('state', 'invalid', 'Must be open or closed');

            expect($exception->getField())->toBe('state');
        });
    });

    describe('getValue', function () {
        it('returns string value', function () {
            $exception = new ValidationException('state', 'invalid', 'error');

            expect($exception->getValue())->toBe('invalid');
        });

        it('returns integer value', function () {
            $exception = new ValidationException('page', -1, 'Must be positive');

            expect($exception->getValue())->toBe(-1);
        });

        it('returns float value', function () {
            $exception = new ValidationException('rate', 1.5, 'Must be integer');

            expect($exception->getValue())->toBe(1.5);
        });

        it('returns null value', function () {
            $exception = new ValidationException('required_field', null, 'Cannot be null');

            expect($exception->getValue())->toBeNull();
        });

        it('returns array value', function () {
            $exception = new ValidationException('labels', ['a', 'b', 'c', 'd', 'e', 'f'], 'Maximum 5 labels allowed');

            expect($exception->getValue())->toBe(['a', 'b', 'c', 'd', 'e', 'f']);
        });

        it('returns boolean value', function () {
            $exception = new ValidationException('is_template', 'yes', 'Must be boolean');

            expect($exception->getValue())->toBe('yes');
        });
    });

    describe('common validation scenarios', function () {
        it('handles invalid repository name', function () {
            $exception = new ValidationException('repository', 'invalid/format/here', 'Repository must be in owner/repo format');

            expect($exception->getField())->toBe('repository')
                ->and($exception->getMessage())->toContain('owner/repo format');
        });

        it('handles invalid pagination', function () {
            $exception = new ValidationException('per_page', 500, 'Must be between 1 and 100');

            expect($exception->getCode())->toBe(422)
                ->and($exception->getValue())->toBe(500);
        });

        it('handles missing required field', function () {
            $exception = new ValidationException('title', '', 'Title is required');

            expect($exception->getValue())->toBe('')
                ->and($exception->getMessage())->toContain('Title is required');
        });

        it('handles invalid enum value', function () {
            $exception = new ValidationException('state', 'pending', 'State must be one of: open, closed');

            expect($exception->getMessage())->toContain('open, closed');
        });
    });

    describe('inheritance', function () {
        it('extends GithubClientException', function () {
            $exception = new ValidationException('field', 'value', 'error');

            expect($exception)->toBeInstanceOf(GithubClientException::class);
        });

        it('inherits addContext functionality', function () {
            $exception = new ValidationException('field', 'value', 'error');
            $exception->addContext('suggestion', 'Use a valid value');

            $context = $exception->getContext();
            expect($context)->toHaveKey('suggestion')
                ->and($context['suggestion'])->toBe('Use a valid value');
        });
    });
});
