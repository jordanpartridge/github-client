<?php

use JordanPartridge\GithubClient\Enums\Issues\Sort;

describe('Issues Sort enum', function () {
    it('has CREATED case with correct value', function () {
        expect(Sort::CREATED->value)->toBe('created');
    });

    it('has UPDATED case with correct value', function () {
        expect(Sort::UPDATED->value)->toBe('updated');
    });

    it('has COMMENTS case with correct value', function () {
        expect(Sort::COMMENTS->value)->toBe('comments');
    });

    it('has exactly three cases', function () {
        expect(Sort::cases())
            ->toHaveCount(3)
            ->and(array_column(Sort::cases(), 'name'))
            ->toContain('CREATED', 'UPDATED', 'COMMENTS');
    });

    it('can be created from valid string values', function () {
        expect(Sort::from('created'))->toBe(Sort::CREATED)
            ->and(Sort::from('updated'))->toBe(Sort::UPDATED)
            ->and(Sort::from('comments'))->toBe(Sort::COMMENTS);
    });

    it('throws ValueError for invalid string value', function () {
        expect(fn () => Sort::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(Sort::tryFrom('invalid'))->toBeNull()
            ->and(Sort::tryFrom('CREATED'))->toBeNull()
            ->and(Sort::tryFrom('pushed'))->toBeNull();
    });

    it('can be used in match expressions', function () {
        $getSortLabel = fn (Sort $sort) => match ($sort) {
            Sort::CREATED => 'Sort by creation date',
            Sort::UPDATED => 'Sort by last update',
            Sort::COMMENTS => 'Sort by comment count',
        };

        expect($getSortLabel(Sort::CREATED))->toBe('Sort by creation date')
            ->and($getSortLabel(Sort::COMMENTS))->toBe('Sort by comment count');
    });

    it('works with array functions', function () {
        $values = array_map(fn (Sort $s) => $s->value, Sort::cases());

        expect($values)->toBe(['created', 'updated', 'comments']);
    });

    it('is distinct from main Sort enum', function () {
        expect(Sort::class)->toBe('JordanPartridge\GithubClient\Enums\Issues\Sort');
    });
});
