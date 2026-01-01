<?php

use JordanPartridge\GithubClient\Data\Pulls\MergeResponseDTO;

it('can create MergeResponseDTO from API response', function () {
    $data = [
        'merged' => true,
        'sha' => 'abc123def456789',
        'message' => 'Pull Request successfully merged',
    ];

    $response = MergeResponseDTO::fromApiResponse($data);

    expect($response->merged)->toBeTrue();
    expect($response->sha)->toBe('abc123def456789');
    expect($response->message)->toBe('Pull Request successfully merged');
});

it('can convert MergeResponseDTO to array', function () {
    $response = new MergeResponseDTO(
        merged: true,
        sha: 'xyz789',
        message: 'Merged successfully',
    );

    $array = $response->toArray();

    expect($array)->toBe([
        'merged' => true,
        'sha' => 'xyz789',
        'message' => 'Merged successfully',
    ]);
});

it('handles failed merge response', function () {
    $data = [
        'merged' => false,
        'sha' => '',
        'message' => 'Pull Request could not be merged',
    ];

    $response = MergeResponseDTO::fromApiResponse($data);

    expect($response->merged)->toBeFalse();
    expect($response->sha)->toBe('');
    expect($response->message)->toBe('Pull Request could not be merged');
});

it('handles missing fields with defaults', function () {
    $data = [];

    $response = MergeResponseDTO::fromApiResponse($data);

    expect($response->merged)->toBeFalse();
    expect($response->sha)->toBe('');
    expect($response->message)->toBe('');
});
