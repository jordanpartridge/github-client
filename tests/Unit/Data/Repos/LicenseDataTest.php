<?php

use JordanPartridge\GithubClient\Data\Repos\LicenseData;

it('can create LicenseData from array', function () {
    $data = [
        'key' => 'mit',
        'name' => 'MIT License',
        'spdx_id' => 'MIT',
        'url' => 'https://api.github.com/licenses/mit',
        'node_id' => 'MDc6TGljZW5zZW1pdA==',
    ];

    $license = LicenseData::fromArray($data);

    expect($license->key)->toBe('mit');
    expect($license->name)->toBe('MIT License');
    expect($license->spdx_id)->toBe('MIT');
    expect($license->url)->toBe('https://api.github.com/licenses/mit');
    expect($license->node_id)->toBe('MDc6TGljZW5zZW1pdA==');
});

it('can convert LicenseData to array', function () {
    $license = new LicenseData(
        key: 'apache-2.0',
        name: 'Apache License 2.0',
        spdx_id: 'Apache-2.0',
        url: 'https://api.github.com/licenses/apache-2.0',
        node_id: 'MDc6TGljZW5zZWFwYWNoZS0yLjA=',
    );

    $array = $license->toArray();

    expect($array)->toBe([
        'key' => 'apache-2.0',
        'name' => 'Apache License 2.0',
        'spdx_id' => 'Apache-2.0',
        'url' => 'https://api.github.com/licenses/apache-2.0',
        'node_id' => 'MDc6TGljZW5zZWFwYWNoZS0yLjA=',
    ]);
});

it('handles null url', function () {
    $data = [
        'key' => 'other',
        'name' => 'Other',
        'spdx_id' => 'NOASSERTION',
        'node_id' => 'MDc6TGljZW5zZW90aGVy',
    ];

    $license = LicenseData::fromArray($data);

    expect($license->url)->toBeNull();
});

it('handles common open source licenses', function () {
    $licenses = [
        ['key' => 'gpl-3.0', 'name' => 'GNU General Public License v3.0', 'spdx_id' => 'GPL-3.0', 'url' => 'https://api.github.com/licenses/gpl-3.0', 'node_id' => 'gpl3'],
        ['key' => 'bsd-3-clause', 'name' => 'BSD 3-Clause "New" or "Revised" License', 'spdx_id' => 'BSD-3-Clause', 'url' => 'https://api.github.com/licenses/bsd-3-clause', 'node_id' => 'bsd3'],
        ['key' => 'isc', 'name' => 'ISC License', 'spdx_id' => 'ISC', 'url' => 'https://api.github.com/licenses/isc', 'node_id' => 'isc'],
    ];

    foreach ($licenses as $licenseData) {
        $license = LicenseData::fromArray($licenseData);
        expect($license->key)->toBe($licenseData['key']);
        expect($license->spdx_id)->toBe($licenseData['spdx_id']);
    }
});
