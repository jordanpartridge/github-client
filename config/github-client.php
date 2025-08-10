<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub Token
    |--------------------------------------------------------------------------
    |
    | GitHub personal access token for authentication. If not provided here,
    | the TokenResolver will attempt to find a token from:
    | 1. GitHub CLI (gh auth token)
    | 2. Environment variables (GITHUB_TOKEN, GH_TOKEN)
    | 3. This config value
    |
    | Authentication is optional for public repositories but recommended
    | for higher rate limits.
    */
    'token' => env('GITHUB_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | GitHub API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the GitHub API. Typically only changed when using
    | GitHub Enterprise Server.
    */
    'base_url' => env('GITHUB_BASE_URL', 'https://api.github.com'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Options
    |--------------------------------------------------------------------------
    |
    | Configuration for authentication behavior.
    */
    'auth' => [
        // Whether to prefer GitHub CLI authentication over environment variables
        'prefer_github_cli' => env('GITHUB_PREFER_CLI_AUTH', true),

        // Whether to show authentication guidance when no token is found
        'show_auth_guidance' => env('GITHUB_SHOW_AUTH_GUIDANCE', true),

        // Environment variables to check for tokens (in priority order)
        'token_env_vars' => ['GITHUB_TOKEN', 'GH_TOKEN'],
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OAuth authentication flow.
    */
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

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuration for handling GitHub API rate limits.
    */
    'rate_limit' => [
        // Whether to automatically retry requests when rate limited
        'auto_retry' => env('GITHUB_AUTO_RETRY', true),

        // Maximum number of retry attempts
        'max_retries' => env('GITHUB_MAX_RETRIES', 3),

        // Whether to respect rate limit headers and wait automatically
        'respect_rate_limits' => env('GITHUB_RESPECT_RATE_LIMITS', true),
    ],
];
