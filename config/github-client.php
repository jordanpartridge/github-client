<?php

return [
    'token' => env('GITHUB_TOKEN'),
    'base_url' => env('GITHUB_BASE_URL', 'https://api.github.com'),

    'oauth' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect_url' => env('GITHUB_REDIRECT_URL'),

        // Default scopes to request
        'scopes' => [
            'repo',
            'user',
            'read:org',
        ],
    ],
];
