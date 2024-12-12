<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub API Token
    |--------------------------------------------------------------------------
    |
    | The personal access token used for authentication with the GitHub API.
    | You can generate this token in your GitHub account settings.
    |
    */
    'token'             => env('GITHUB_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Default Connector
    |--------------------------------------------------------------------------
    |
    | Choose the default connector type to use when not explicitly specified.
    | Options: 'rest', 'graphql'
    |
    */
    'default_connector' => 'rest',

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure how to handle GitHub API rate limits.
    |
    */
    'rate_limit'        => [
        'retry'       => true,
        'max_retries' => 3,
    ],
];
