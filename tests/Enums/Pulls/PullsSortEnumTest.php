<?php

use JordanPartridge\GithubClient\Enums\Pulls\Sort;

describe('Pulls Sort enum', function () {
    it('has CREATED case with correct value', function () {
        expect(Sort::CREATED->value)->toBe('created');
    });

    it('has UPDATED case with correct value', function () {
        expect(Sort::UPDATED->value)->toBe('updated');
    });

    it('has POPULARITY case with correct value', function () {
        expect(Sort::POPULARITY->value)->toBe('popularity');
    });

    it('has LONG_RUNNING case with correct value', function () {
        expect(Sort::LONG_RUNNING->value)->toBe('long-running');
    });

    it('has exactly four cases', function () {
        expect(Sort::cases())
            ->toHaveCount(4)
            ->and(array_column(Sort::cases(), 'name'))
            ->toContain('CREATED', 'UPDATED', 'POPULARITY', 'LONG_RUNNING');
    });

    it('can be created from valid string values', function () {
        expect(Sort::from('created'))->toBe(Sort::CREATED)
            ->and(Sort::from('updated'))->toBe(Sort::UPDATED)
            ->and(Sort::from('popularity'))->toBe(Sort::POPULARITY)
            ->and(Sort::from('long-running'))->toBe(Sort::LONG_RUNNING);
    });

    it('throws ValueError for invalid string value', function () {
        expect(fn () => Sort::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(Sort::tryFrom('invalid'))->toBeNull()
            ->and(Sort::tryFrom('CREATED'))->toBeNull()
            ->and(Sort::tryFrom('long_running'))->toBeNull();
    });

    it('handles hyphenated value correctly', function () {
        $sort = Sort::from('long-running');

        expect($sort)->toBe(Sort::LONG_RUNNING)
            ->and($sort->value)->toBe('long-running')
            ->and($sort->name)->toBe('LONG_RUNNING');
    });

    it('can be used in match expressions', function () {
        $getSortLabel = fn (Sort $sort) => match ($sort) {
            Sort::CREATED => 'Sort by creation date',
            Sort::UPDATED => 'Sort by last update',
            Sort::POPULARITY => 'Sort by popularity',
            Sort::LONG_RUNNING => 'Sort by long running',
        };

        expect($getSortLabel(Sort::CREATED))->toBe('Sort by creation date')
            ->and($getSortLabel(Sort::LONG_RUNNING))->toBe('Sort by long running');
    });

    it('works with array functions', function () {
        $values = array_map(fn (Sort $s) => $s->value, Sort::cases());

        expect($values)->toBe(['created', 'updated', 'popularity', 'long-running']);
    });

    it('is distinct from Issues Sort enum', function () {
        expect(Sort::class)->toBe('JordanPartridge\GithubClient\Enums\Pulls\Sort');
    });

    it('has different cases than Issues Sort', function () {
        $pullsSortValues = array_column(Sort::cases(), 'value');

        expect($pullsSortValues)
            ->toContain('popularity', 'long-running')
            ->not->toContain('comments');
    });
});
