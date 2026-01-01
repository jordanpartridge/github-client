<?php

use JordanPartridge\GithubClient\Enums\Pulls\State;

describe('Pulls State enum', function () {
    it('has OPEN case with correct value', function () {
        expect(State::OPEN->value)->toBe('open');
    });

    it('has CLOSED case with correct value', function () {
        expect(State::CLOSED->value)->toBe('closed');
    });

    it('has ALL case with correct value', function () {
        expect(State::ALL->value)->toBe('all');
    });

    it('has exactly three cases', function () {
        expect(State::cases())
            ->toHaveCount(3)
            ->and(array_column(State::cases(), 'name'))
            ->toContain('OPEN', 'CLOSED', 'ALL');
    });

    it('can be created from valid string values', function () {
        expect(State::from('open'))->toBe(State::OPEN)
            ->and(State::from('closed'))->toBe(State::CLOSED)
            ->and(State::from('all'))->toBe(State::ALL);
    });

    it('throws ValueError for invalid string value', function () {
        expect(fn () => State::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(State::tryFrom('invalid'))->toBeNull()
            ->and(State::tryFrom('OPEN'))->toBeNull()
            ->and(State::tryFrom('merged'))->toBeNull();
    });

    it('can be used in match expressions', function () {
        $getStateDescription = fn (State $state) => match ($state) {
            State::OPEN => 'Open pull requests',
            State::CLOSED => 'Closed pull requests',
            State::ALL => 'All pull requests',
        };

        expect($getStateDescription(State::OPEN))->toBe('Open pull requests')
            ->and($getStateDescription(State::ALL))->toBe('All pull requests');
    });

    it('works with array functions', function () {
        $values = array_map(fn (State $s) => $s->value, State::cases());

        expect($values)->toBe(['open', 'closed', 'all']);
    });

    it('is distinct from Issues State enum', function () {
        expect(State::class)->toBe('JordanPartridge\GithubClient\Enums\Pulls\State');
    });

    it('has same case values as Issues State', function () {
        $pullsStateValues = array_column(State::cases(), 'value');

        expect($pullsStateValues)->toBe(['open', 'closed', 'all']);
    });

    it('can filter to only terminal states', function () {
        $terminalStates = array_filter(
            State::cases(),
            fn (State $state) => $state !== State::ALL,
        );

        expect($terminalStates)->toHaveCount(2);
    });
});
