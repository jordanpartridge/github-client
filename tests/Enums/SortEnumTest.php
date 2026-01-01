<?php

use JordanPartridge\GithubClient\Enums\Sort;

describe('Sort enum (Repos)', function () {
    it('has CREATED case with correct value', function () {
        expect(Sort::CREATED->value)->toBe('created');
    });

    it('has UPDATED case with correct value', function () {
        expect(Sort::UPDATED->value)->toBe('updated');
    });

    it('has PUSHED case with correct value', function () {
        expect(Sort::PUSHED->value)->toBe('pushed');
    });

    it('has FULL_NAME case with correct value', function () {
        expect(Sort::FULL_NAME->value)->toBe('full_name');
    });

    it('has exactly four cases', function () {
        expect(Sort::cases())
            ->toHaveCount(4)
            ->and(array_column(Sort::cases(), 'name'))
            ->toContain('CREATED', 'UPDATED', 'PUSHED', 'FULL_NAME');
    });

    it('can be created from valid string values', function () {
        expect(Sort::from('created'))->toBe(Sort::CREATED)
            ->and(Sort::from('updated'))->toBe(Sort::UPDATED)
            ->and(Sort::from('pushed'))->toBe(Sort::PUSHED)
            ->and(Sort::from('full_name'))->toBe(Sort::FULL_NAME);
    });

    it('throws ValueError for invalid string value', function () {
        expect(fn () => Sort::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(Sort::tryFrom('invalid'))->toBeNull()
            ->and(Sort::tryFrom('CREATED'))->toBeNull();
    });

    it('can be used in match expressions', function () {
        $getSortLabel = fn (Sort $sort) => match ($sort) {
            Sort::CREATED => 'Creation Date',
            Sort::UPDATED => 'Last Updated',
            Sort::PUSHED => 'Last Pushed',
            Sort::FULL_NAME => 'Repository Name',
        };

        expect($getSortLabel(Sort::CREATED))->toBe('Creation Date')
            ->and($getSortLabel(Sort::FULL_NAME))->toBe('Repository Name');
    });

    it('works with array functions', function () {
        $values = array_map(fn (Sort $s) => $s->value, Sort::cases());

        expect($values)->toBe(['created', 'updated', 'pushed', 'full_name']);
    });

    it('can be compared with other Sort values', function () {
        expect(Sort::CREATED === Sort::CREATED)->toBeTrue()
            ->and(Sort::CREATED === Sort::UPDATED)->toBeFalse();
    });
});
