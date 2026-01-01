<?php

use JordanPartridge\GithubClient\Data\Issues\LabelDTO;

it('can create LabelDTO from API response', function () {
    $data = [
        'id' => 12345,
        'name' => 'bug',
        'color' => 'ff0000',
        'description' => 'Something is not working',
        'default' => false,
    ];

    $label = LabelDTO::fromApiResponse($data);

    expect($label->id)->toBe(12345);
    expect($label->name)->toBe('bug');
    expect($label->color)->toBe('ff0000');
    expect($label->description)->toBe('Something is not working');
    expect($label->default)->toBeFalse();
});

it('can convert LabelDTO to array', function () {
    $label = new LabelDTO(
        id: 54321,
        name: 'enhancement',
        color: '00ff00',
        description: 'New feature request',
        default: false,
    );

    $array = $label->toArray();

    expect($array)->toBe([
        'id' => 54321,
        'name' => 'enhancement',
        'color' => '00ff00',
        'description' => 'New feature request',
        'default' => false,
    ]);
});

it('handles null description', function () {
    $data = [
        'id' => 111,
        'name' => 'wontfix',
        'color' => '999999',
        'default' => true,
    ];

    $label = LabelDTO::fromApiResponse($data);

    expect($label->description)->toBeNull();
    expect($label->default)->toBeTrue();
});

it('handles default labels', function () {
    $data = [
        'id' => 222,
        'name' => 'documentation',
        'color' => '0075ca',
        'description' => 'Improvements or additions to documentation',
        'default' => true,
    ];

    $label = LabelDTO::fromApiResponse($data);

    expect($label->default)->toBeTrue();
});

it('handles missing default field', function () {
    $data = [
        'id' => 333,
        'name' => 'custom',
        'color' => 'abcdef',
        'description' => 'Custom label',
    ];

    $label = LabelDTO::fromApiResponse($data);

    expect($label->default)->toBeFalse();
});
