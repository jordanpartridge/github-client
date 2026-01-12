<?php

use JordanPartridge\GithubClient\Enums\Repos\Type;

describe('Repos Type enum', function () {
    it('has All case with correct value', function () {
        expect(Type::All->value)->toBe('all');
    });

    it('has Public case with correct value', function () {
        expect(Type::Public->value)->toBe('public');
    });

    it('has Private case with correct value', function () {
        expect(Type::Private->value)->toBe('private');
    });

    it('has Forks case with correct value', function () {
        expect(Type::Forks->value)->toBe('forks');
    });

    it('has Sources case with correct value', function () {
        expect(Type::Sources->value)->toBe('sources');
    });

    it('has Member case with correct value', function () {
        expect(Type::Member->value)->toBe('member');
    });

    it('has Owner case with correct value', function () {
        expect(Type::Owner->value)->toBe('owner');
    });

    it('has exactly seven cases', function () {
        expect(Type::cases())
            ->toHaveCount(7)
            ->and(array_column(Type::cases(), 'name'))
            ->toContain('All', 'Public', 'Private', 'Forks', 'Sources', 'Member', 'Owner');
    });

    it('can be created from valid string values', function () {
        expect(Type::from('all'))->toBe(Type::All)
            ->and(Type::from('public'))->toBe(Type::Public)
            ->and(Type::from('private'))->toBe(Type::Private)
            ->and(Type::from('forks'))->toBe(Type::Forks)
            ->and(Type::from('sources'))->toBe(Type::Sources)
            ->and(Type::from('member'))->toBe(Type::Member)
            ->and(Type::from('owner'))->toBe(Type::Owner);
    });

    it('throws ValueError for invalid string value', function () {
        expect(fn () => Type::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(Type::tryFrom('invalid'))->toBeNull()
            ->and(Type::tryFrom('All'))->toBeNull()
            ->and(Type::tryFrom('PUBLIC'))->toBeNull();
    });

    it('can be used in match expressions', function () {
        $getTypeDescription = fn (Type $type) => match ($type) {
            Type::All => 'All repositories',
            Type::Public => 'Public repositories only',
            Type::Private => 'Private repositories only',
            Type::Forks => 'Forked repositories only',
            Type::Sources => 'Source repositories only',
            Type::Member => 'Member repositories',
            Type::Owner => 'Owner repositories',
        };

        expect($getTypeDescription(Type::All))->toBe('All repositories')
            ->and($getTypeDescription(Type::Forks))->toBe('Forked repositories only')
            ->and($getTypeDescription(Type::Owner))->toBe('Owner repositories');
    });

    it('works with array functions', function () {
        $values = array_map(fn (Type $t) => $t->value, Type::cases());

        expect($values)->toBe(['all', 'public', 'private', 'forks', 'sources', 'member', 'owner']);
    });

    it('uses PascalCase for case names', function () {
        foreach (Type::cases() as $case) {
            expect($case->name)->toMatch('/^[A-Z][a-z]*$/');
        }
    });

    it('can filter visibility-related types', function () {
        $visibilityTypes = array_filter(
            Type::cases(),
            fn (Type $type) => in_array($type, [Type::Public, Type::Private]),
        );

        expect($visibilityTypes)->toHaveCount(2);
    });

    it('can filter ownership-related types', function () {
        $ownershipTypes = array_filter(
            Type::cases(),
            fn (Type $type) => in_array($type, [Type::Member, Type::Owner]),
        );

        expect($ownershipTypes)->toHaveCount(2);
    });

    it('can filter origin-related types', function () {
        $originTypes = array_filter(
            Type::cases(),
            fn (Type $type) => in_array($type, [Type::Forks, Type::Sources]),
        );

        expect($originTypes)->toHaveCount(2);
    });
});
