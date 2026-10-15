# Authentication and errors

## Personal access token

```env
GITHUB_TOKEN=ghp_your_token_here
GH_TOKEN=
```

`config/github-client.php` copies those into `token` and `gh_token`. `TokenResolver` uses `token`, then `gh_token`, then the GitHub CLI.

The connector base URL is `https://api.github.com`. The package does not read a base URL from config.

## OAuth

```env
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URL=
```

```php
use JordanPartridge\GithubClient\Facades\GithubOAuth;

$url = GithubOAuth::getAuthorizationUrl(['repo'], $state);
$token = GithubOAuth::getAccessToken($code);
```

Default scopes are `['repo']` when you omit the argument. Config does not supply scopes. The package does not persist `state`. Pass a value you stored, and compare it on the callback.

`getAccessToken()` posts the code to `https://github.com/login/oauth/access_token` and returns the `access_token` string. A body without that field throws `AuthenticationException`.

## GitHub App

```env
GITHUB_APP_ID=
GITHUB_APP_PRIVATE_KEY=
GITHUB_APP_PRIVATE_KEY_PATH=
```

`Github::forInstallation($id)` and `Github::withApp($appId, $privateKey, $installationId)` return a new client. They are on the default branch and are not in the `v3.0.0` tag. The private key may be PEM text or a file path.

## Errors

| Status | Exception |
| --- | --- |
| 401 | `JordanPartridge\GithubClient\Exceptions\AuthenticationException` |
| 403 when the body or headers say the rate limit is spent | `RateLimitException` |
| 403 otherwise | `ApiException` |
| 404 | `ResourceNotFoundException` |
| 422 | `ValidationException` |
| 429 | `RateLimitException` |
| 500, 502, 503, 504 | `NetworkException` |
| other | `ApiException` |

There is no `withRateLimiting()` helper and no `Github::fake()`. In tests, call `Github::connector()->withMockClient()` with a Saloon `MockClient`. The mock body has to include the fields the DTO constructor reads. See `tests/DocumentationExamplesTest.php`.

## What the config file does not do

These are not implemented. They are not in the published config.

- A custom API base URL
- Automatic retries
- A `prefer_github_cli` switch
- OAuth scopes from config
- A default GitHub App installation id
