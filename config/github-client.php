<?php

return [
    'authentication' => [
        'default' => env('GITHUB_AUTH_METHOD', 'token'),
        'token' => [
            'access_token' => env('GITHUB_TOKEN'),
        ],
        'app' => [
            'app_id' => env('GITHUB_APP_ID'),
            'installation_id' => env('GITHUB_INSTALLATION_ID'),
            'private_key_path' => env('GITHUB_APP_PRIVATE_KEY_PATH'),
        ],
    ],
    'api' => [
        'base_url' => 'https://api.github.com',
        'timeout' => 30,
    ],
];