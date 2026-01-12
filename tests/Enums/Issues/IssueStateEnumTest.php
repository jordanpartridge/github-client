<?php

use JordanPartridge\GithubClient\Enums\Issues\State;

describe('Issues State enum', function () {
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
            ->and(State::tryFrom('pending'))->toBeNull();
    });

    it('can be used in match expressions', function () {
        $getStateDescription = fn (State $state) => match ($state) {
            State::OPEN => 'Open issues only',
            State::CLOSED => 'Closed issues only',
            State::ALL => 'All issues',
        };

        expect($getStateDescription(State::OPEN))->toBe('Open issues only')
            ->and($getStateDescription(State::ALL))->toBe('All issues');
    });

    it('works with array functions', function () {
        $values = array_map(fn (State $s) => $s->value, State::cases());

        expect($values)->toBe(['open', 'closed', 'all']);
    });

    it('is distinct from Pulls State enum', function () {
        expect(State::class)->toBe('JordanPartridge\GithubClient\Enums\Issues\State');
    });

    it('can filter to only active states', function () {
        $activeStates = array_filter(
            State::cases(),
            fn (State $state) => $state !== State::ALL,
        );

        expect($activeStates)->toHaveCount(2)
            ->and(array_column($activeStates, 'value'))->toContain('open', 'closed');
    });
});
