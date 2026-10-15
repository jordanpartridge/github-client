<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub Token
    |--------------------------------------------------------------------------
    |
    | TokenResolver reads these config values, then `gh auth token`.
    | `token` wins when both are set. Leave a value empty to skip it.
    | Public repositories can be requested with no token.
    |
    */
    'token' => env('GITHUB_TOKEN'),

    'gh_token' => env('GH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | OAuth
    |--------------------------------------------------------------------------
    |
    | Used by the GithubOAuth facade. Scopes are an argument to
    | getAuthorizationUrl(), not a config value.
    |
    */
    'oauth' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect_url' => env('GITHUB_REDIRECT_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub App
    |--------------------------------------------------------------------------
    |
    | Used by Github::forInstallation() and Github::withApp().
    | Pass the installation id to forInstallation(). This file does not
    | store one. The private key may be PEM text or a filesystem path.
    |
    */
    'github_app' => [
        'app_id' => env('GITHUB_APP_ID'),

        'private_key' => env('GITHUB_APP_PRIVATE_KEY'),

        'private_key_path' => env('GITHUB_APP_PRIVATE_KEY_PATH'),
    ],
];
