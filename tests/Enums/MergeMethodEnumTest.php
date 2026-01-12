<?php

use JordanPartridge\GithubClient\Enums\MergeMethod;

describe('MergeMethod enum', function () {
    it('has Merge case with correct value', function () {
        expect(MergeMethod::Merge->value)->toBe('merge');
    });

    it('has Squash case with correct value', function () {
        expect(MergeMethod::Squash->value)->toBe('squash');
    });

    it('has Rebase case with correct value', function () {
        expect(MergeMethod::Rebase->value)->toBe('rebase');
    });

    it('has exactly three cases', function () {
        expect(MergeMethod::cases())
            ->toHaveCount(3)
            ->and(array_column(MergeMethod::cases(), 'name'))
            ->toContain('Merge', 'Squash', 'Rebase');
    });

    it('can be created from valid string values', function () {
        expect(MergeMethod::from('merge'))->toBe(MergeMethod::Merge)
            ->and(MergeMethod::from('squash'))->toBe(MergeMethod::Squash)
            ->and(MergeMethod::from('rebase'))->toBe(MergeMethod::Rebase);
    });

    it('throws ValueError for invalid string value', function () {
        expect(fn () => MergeMethod::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(MergeMethod::tryFrom('invalid'))->toBeNull()
            ->and(MergeMethod::tryFrom('MERGE'))->toBeNull()
            ->and(MergeMethod::tryFrom('Merge'))->toBeNull();
    });

    it('can be used in match expressions', function () {
        $getDescription = fn (MergeMethod $method) => match ($method) {
            MergeMethod::Merge => 'Merge all commits',
            MergeMethod::Squash => 'Squash all commits into one',
            MergeMethod::Rebase => 'Rebase commits onto base branch',
        };

        expect($getDescription(MergeMethod::Merge))->toBe('Merge all commits')
            ->and($getDescription(MergeMethod::Squash))->toBe('Squash all commits into one')
            ->and($getDescription(MergeMethod::Rebase))->toBe('Rebase commits onto base branch');
    });

    it('works with array functions', function () {
        $values = array_map(fn (MergeMethod $m) => $m->value, MergeMethod::cases());

        expect($values)->toBe(['merge', 'squash', 'rebase']);
    });

    it('can be compared with other MergeMethod values', function () {
        expect(MergeMethod::Merge === MergeMethod::Merge)->toBeTrue()
            ->and(MergeMethod::Merge === MergeMethod::Squash)->toBeFalse()
            ->and(MergeMethod::Squash !== MergeMethod::Rebase)->toBeTrue();
    });

    it('uses PascalCase for case names', function () {
        foreach (MergeMethod::cases() as $case) {
            expect($case->name)->toMatch('/^[A-Z][a-z]+$/');
        }
    });
});
