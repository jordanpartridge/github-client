<?php

use JordanPartridge\GithubClient\Enums\Repos\State;

describe('Repos State enum', function () {
    it('has OPEN case', function () {
        expect(State::OPEN->name)->toBe('OPEN');
    });

    it('has CLOSED case', function () {
        expect(State::CLOSED->name)->toBe('CLOSED');
    });

    it('has ALL case', function () {
        expect(State::ALL->name)->toBe('ALL');
    });

    it('has exactly three cases', function () {
        expect(State::cases())
            ->toHaveCount(3)
            ->and(array_column(State::cases(), 'name'))
            ->toContain('OPEN', 'CLOSED', 'ALL');
    });

    it('is a unit enum without values', function () {
        $case = State::OPEN;

        expect($case)->toBeInstanceOf(State::class)
            ->and(property_exists($case, 'value'))->toBeFalse();
    });

    it('can be used in match expressions', function () {
        $getStateLabel = fn (State $state) => match ($state) {
            State::OPEN => 'Open items',
            State::CLOSED => 'Closed items',
            State::ALL => 'All items',
        };

        expect($getStateLabel(State::OPEN))->toBe('Open items')
            ->and($getStateLabel(State::ALL))->toBe('All items');
    });

    it('works with array functions', function () {
        $names = array_map(fn (State $s) => $s->name, State::cases());

        expect($names)->toBe(['OPEN', 'CLOSED', 'ALL']);
    });

    it('is distinct from Issues and Pulls State enums', function () {
        expect(State::class)->toBe('JordanPartridge\GithubClient\Enums\Repos\State');
    });

    it('can filter to only terminal states', function () {
        $terminalStates = array_filter(
            State::cases(),
            fn (State $state) => $state !== State::ALL,
        );

        expect($terminalStates)->toHaveCount(2);
    });

    it('can be compared for equality', function () {
        expect(State::OPEN === State::OPEN)->toBeTrue()
            ->and(State::OPEN === State::CLOSED)->toBeFalse();
    });

    it('does not have from or tryFrom methods since it is a unit enum', function () {
        expect(method_exists(State::class, 'from'))->toBeFalse()
            ->and(method_exists(State::class, 'tryFrom'))->toBeFalse();
    });
});
