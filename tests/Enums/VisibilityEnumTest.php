<?php

use JordanPartridge\GithubClient\Enums\Visibility;

describe('Visibility enum', function () {
    it('has PUBLIC case with correct value', function () {
        expect(Visibility::PUBLIC->value)->toBe('public');
    });

    it('has PRIVATE case with correct value', function () {
        expect(Visibility::PRIVATE->value)->toBe('private');
    });

    it('has INTERNAL case with correct value', function () {
        expect(Visibility::INTERNAL->value)->toBe('internal');
    });

    it('has exactly three cases', function () {
        expect(Visibility::cases())
            ->toHaveCount(3)
            ->and(array_column(Visibility::cases(), 'name'))
            ->toContain('PUBLIC', 'PRIVATE', 'INTERNAL');
    });

    it('can be created from valid string values', function () {
        expect(Visibility::from('public'))->toBe(Visibility::PUBLIC)
            ->and(Visibility::from('private'))->toBe(Visibility::PRIVATE)
            ->and(Visibility::from('internal'))->toBe(Visibility::INTERNAL);
    });

    it('throws ValueError for invalid string value', function () {
        expect(fn () => Visibility::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(Visibility::tryFrom('invalid'))->toBeNull()
            ->and(Visibility::tryFrom('PUBLIC'))->toBeNull()
            ->and(Visibility::tryFrom('Private'))->toBeNull();
    });

    it('can be used in match expressions', function () {
        $getDescription = fn (Visibility $v) => match ($v) {
            Visibility::PUBLIC => 'Visible to everyone',
            Visibility::PRIVATE => 'Visible only to collaborators',
            Visibility::INTERNAL => 'Visible to organization members',
        };

        expect($getDescription(Visibility::PUBLIC))->toBe('Visible to everyone')
            ->and($getDescription(Visibility::PRIVATE))->toBe('Visible only to collaborators')
            ->and($getDescription(Visibility::INTERNAL))->toBe('Visible to organization members');
    });

    it('works with array functions', function () {
        $values = array_map(fn (Visibility $v) => $v->value, Visibility::cases());

        expect($values)->toBe(['public', 'private', 'internal']);
    });

    it('can be compared with other Visibility values', function () {
        expect(Visibility::PUBLIC === Visibility::PUBLIC)->toBeTrue()
            ->and(Visibility::PUBLIC === Visibility::PRIVATE)->toBeFalse()
            ->and(Visibility::PRIVATE !== Visibility::INTERNAL)->toBeTrue();
    });

    it('uses SCREAMING_SNAKE_CASE for case names', function () {
        foreach (Visibility::cases() as $case) {
            expect($case->name)->toMatch('/^[A-Z]+$/');
        }
    });
});
