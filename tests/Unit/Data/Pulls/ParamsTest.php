<?php

use JordanPartridge\GithubClient\Data\Pulls\Params;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Pulls\Sort;
use JordanPartridge\GithubClient\Enums\Pulls\State;

it('can create Params from array with all fields', function () {
    $data = [
        'state' => 'open',
        'head' => 'user:feature-branch',
        'base' => 'main',
        'sort' => 'created',
        'direction' => 'desc',
        'per_page' => '30',
        'page' => '1',
    ];

    $params = Params::fromArray($data);

    expect($params->state)->toBe(State::OPEN);
    expect($params->head)->toBe('user:feature-branch');
    expect($params->base)->toBe('main');
    expect($params->sort)->toBe(Sort::CREATED);
    expect($params->direction)->toBe(Direction::DESC);
    expect($params->per_page)->toBe('30');
    expect($params->page)->toBe('1');
});

it('can convert Params to array', function () {
    $params = new Params(
        state: State::CLOSED,
        head: 'user:branch',
        base: 'develop',
        sort: Sort::UPDATED,
        direction: Direction::ASC,
        per_page: '50',
        page: '2',
    );

    $array = $params->toArray();

    expect($array['state'])->toBe('closed');
    expect($array['head'])->toBe('user:branch');
    expect($array['base'])->toBe('develop');
    expect($array['sort'])->toBe('updated');
    expect($array['direction'])->toBe('asc');
    expect($array['per_page'])->toBe('50');
    expect($array['page'])->toBe('2');
});

it('handles null fields', function () {
    $data = [];

    $params = Params::fromArray($data);

    expect($params->state)->toBeNull();
    expect($params->head)->toBeNull();
    expect($params->base)->toBeNull();
    expect($params->sort)->toBeNull();
    expect($params->direction)->toBeNull();
    expect($params->per_page)->toBeNull();
    expect($params->page)->toBeNull();
});

it('converts null fields to null in array', function () {
    $params = new Params(
        state: null,
        head: null,
        base: null,
        sort: null,
        direction: null,
        per_page: null,
        page: null,
    );

    $array = $params->toArray();

    expect($array['state'])->toBeNull();
    expect($array['head'])->toBeNull();
    expect($array['base'])->toBeNull();
    expect($array['sort'])->toBeNull();
    expect($array['direction'])->toBeNull();
    expect($array['per_page'])->toBeNull();
    expect($array['page'])->toBeNull();
});

it('handles all state values', function () {
    $openParams = Params::fromArray(['state' => 'open']);
    $closedParams = Params::fromArray(['state' => 'closed']);
    $allParams = Params::fromArray(['state' => 'all']);

    expect($openParams->state)->toBe(State::OPEN);
    expect($closedParams->state)->toBe(State::CLOSED);
    expect($allParams->state)->toBe(State::ALL);
});

it('handles all sort values', function () {
    $createdParams = Params::fromArray(['sort' => 'created']);
    $updatedParams = Params::fromArray(['sort' => 'updated']);
    $popularityParams = Params::fromArray(['sort' => 'popularity']);
    $longRunningParams = Params::fromArray(['sort' => 'long-running']);

    expect($createdParams->sort)->toBe(Sort::CREATED);
    expect($updatedParams->sort)->toBe(Sort::UPDATED);
    expect($popularityParams->sort)->toBe(Sort::POPULARITY);
    expect($longRunningParams->sort)->toBe(Sort::LONG_RUNNING);
});

it('handles all direction values', function () {
    $ascParams = Params::fromArray(['direction' => 'asc']);
    $descParams = Params::fromArray(['direction' => 'desc']);

    expect($ascParams->direction)->toBe(Direction::ASC);
    expect($descParams->direction)->toBe(Direction::DESC);
});
