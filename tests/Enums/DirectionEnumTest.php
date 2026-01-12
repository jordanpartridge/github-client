<?php

use JordanPartridge\GithubClient\Enums\Direction;

describe('Direction enum', function () {
    it('has ASC case with correct value', function () {
        expect(Direction::ASC->value)->toBe('asc');
    });

    it('has DESC case with correct value', function () {
        expect(Direction::DESC->value)->toBe('desc');
    });

    it('has exactly two cases', function () {
        expect(Direction::cases())
            ->toHaveCount(2)
            ->and(array_column(Direction::cases(), 'name'))
            ->toContain('ASC', 'DESC');
    });

    it('can be created from valid string values', function () {
        expect(Direction::from('asc'))->toBe(Direction::ASC)
            ->and(Direction::from('desc'))->toBe(Direction::DESC);
    });

    it('throws ValueError for invalid string value', function () {
        expect(fn () => Direction::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(Direction::tryFrom('invalid'))->toBeNull()
            ->and(Direction::tryFrom('ASC'))->toBeNull()
            ->and(Direction::tryFrom('DESC'))->toBeNull();
    });

    it('can be used in match expressions', function () {
        $result = match (Direction::ASC) {
            Direction::ASC => 'ascending',
            Direction::DESC => 'descending',
        };

        expect($result)->toBe('ascending');
    });

    it('works with array functions', function () {
        $values = array_map(fn (Direction $d) => $d->value, Direction::cases());

        expect($values)->toBe(['asc', 'desc']);
    });
});
