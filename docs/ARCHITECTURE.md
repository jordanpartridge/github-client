# Architecture

`jordanpartridge/github-client` is a Laravel package for the GitHub REST API. It is built on [Saloon](https://docs.saloon.dev) and returns typed PHP objects instead of raw arrays. It requires PHP 8.2 or newer and Laravel 11, 12, or 13. CI runs the suite on PHP 8.2, 8.3, and 8.4.

## 3-Way CLI Pattern

This package implements a **3-Way CLI Pattern** with three complementary ways to access the GitHub API:

1. **Facade** (`Github::`) - Static access for convenience
2. **Resources** (`repos()`, `issues()`) - Grouped methods by feature area
3. **CLI Command** (`php artisan github-client`) - Command-line interface

The `Github` class is the **core package** that orchestrates all three access patterns. See [docs/3WAY-CLI-PATTERN.md](./3WAY-CLI-PATTERN.md) for a detailed explanation.

## Layers

```
Facade (Github::)  ->  Github client  ->  Resource (repos, pullRequests, issues, ...)
                                          ->  Request class (one per endpoint)
                                              ->  GithubConnector (base URL, headers, auth, errors)
                                              <-  createDtoFromResponse() -> typed Data object
```

| Layer | Responsibility | Example |
| --- | --- | --- |
| Connector | Base URL, API version header, authentication, mapping HTTP errors to exceptions | [`src/Connectors/GithubConnector.php`](../src/Connectors/GithubConnector.php) |
| Requests | One class per endpoint: method, path, query, and response mapping | [`src/Requests/Pulls/Index.php`](../src/Requests/Pulls/Index.php) |
| Resources | Developer-facing methods grouped by API area | [`src/Resources/RepoResource.php`](../src/Resources/RepoResource.php) |
| Data | Typed objects built from responses | [`src/Data/Repos/RepoData.php`](../src/Data/Repos/RepoData.php) |
| Value objects and enums | Validated inputs and closed sets of options | [`src/ValueObjects/Repo.php`](../src/ValueObjects/Repo.php), [`src/Enums/MergeMethod.php`](../src/Enums/MergeMethod.php) |

## Key design choices

### 1. One connector owns every cross-cutting concern

[`GithubConnector`](../src/Connectors/GithubConnector.php) sets the base URL, the `X-GitHub-Api-Version` header, and authentication. Its `getRequestException()` maps status codes to specific exceptions with a `match`: 401 to `AuthenticationException`, 403 to a rate-limit or permission error, 404 to `ResourceNotFoundException`, 422 to `ValidationException` with field-level details, 429 to `RateLimitException`, and 5xx to `NetworkException`. Calling code catches a meaningful type instead of checking status codes.

### 2. One request class per endpoint, grouped behind resources

Each endpoint is a small Saloon `Request` that knows its HTTP method, path, query, and how to turn the response into objects (`createDtoFromResponse()`). Resources such as [`RepoResource`](../src/Resources/RepoResource.php) and [`PullRequestResource`](../src/Resources/PullRequestResource.php) extend a `readonly` [`BaseResource`](../src/Resources/BaseResource.php) and give callers methods like `Github::pullRequests()->merge(...)`. Adding an endpoint means adding one request class and one resource method.

### 3. Typed responses and validated inputs

Responses map onto classes in [`src/Data`](../src/Data) with typed properties and `Carbon` dates, so IDEs and static analysis know what comes back. Inputs are validated at the edge: `Repo::fromFullName('owner/repo')` rejects malformed names before any HTTP call, and backed enums (`MergeMethod`, `Visibility`, `Direction`, `Pulls\State`) replace free-form strings for options.

### 4. Pluggable authentication

[`AuthenticationStrategy`](../src/Auth/AuthenticationStrategy.php) is a small interface (`getAuthorizationHeader`, `validate`, `needsRefresh`, `refresh`). The connector accepts either a token string or a strategy. [`GitHubAppAuthentication`](../src/Auth/GitHubAppAuthentication.php) signs an RS256 JWT with the App private key, exchanges it for an installation token, and refreshes that token before it expires. With no explicit credentials, [`TokenResolver`](../src/Auth/TokenResolver.php) tries `config('github-client.token')` (normally `GITHUB_TOKEN`), then `config('github-client.gh_token')` (normally `GH_TOKEN`), then `gh auth token`, and falls back to unauthenticated access for public data.

```php
$github = Github::forInstallation($installationId);
$github->pullRequests()->all('owner', 'repo');
```

### 5. Native Laravel integration

[`GithubClientServiceProvider`](../src/GithubClientServiceProvider.php) uses `spatie/laravel-package-tools` for the config file and an Artisan command, and registers the connector as a singleton in the container. The package is auto-discovered, and the [`Github` facade](../src/Facades/Github.php) carries `@method` annotations so IDEs autocomplete every resource. The same client can be injected by type for code that avoids facades.

### 6. Tests and CI

* Pest tests use Saloon's `MockClient`, so no test touches the network.
* `Process::fake()` in [`tests/Pest.php`](../tests/Pest.php) keeps the GitHub CLI out of the test run.
* Architecture tests in [`tests/ArchTest.php`](../tests/ArchTest.php) enforce that resources extend `BaseResource`, requests extend Saloon `Request`, and no debug calls ship.
* GitHub Actions runs the suite across PHP 8.2, 8.3, 8.4 and Laravel 11, 12, 13 ([`run-tests.yml`](../.github/workflows/run-tests.yml)), plus PHPStan (Larastan) and Laravel Pint.
* Dependabot keeps Composer and Actions dependencies current. Minor and patch updates are set to auto-merge.

## Usage

```php
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Enums\MergeMethod;

$repo = Github::getRepo('laravel/framework');           // RepoData
$prs  = Github::pullRequests()->all('laravel', 'framework');
Github::pullRequests()->merge('owner', 'repo', 42, mergeMethod: MergeMethod::Squash);
```

## Where to look

| Topic | File |
| --- | --- |
| Entry point | [`src/Github.php`](../src/Github.php) |
| HTTP, auth, error mapping | [`src/Connectors/GithubConnector.php`](../src/Connectors/GithubConnector.php) |
| Endpoint example | [`src/Requests/Pulls/Index.php`](../src/Requests/Pulls/Index.php) |
| Resource example | [`src/Resources/PullRequestResource.php`](../src/Resources/PullRequestResource.php) |
| Typed response | [`src/Data/Repos/RepoData.php`](../src/Data/Repos/RepoData.php) |
| Input validation | [`src/ValueObjects/Repo.php`](../src/ValueObjects/Repo.php) |
| GitHub App auth | [`src/Auth/GitHubAppAuthentication.php`](../src/Auth/GitHubAppAuthentication.php) |
| Container wiring | [`src/GithubClientServiceProvider.php`](../src/GithubClientServiceProvider.php) |
| Test conventions | [`tests/Pest.php`](../tests/Pest.php), [`tests/ArchTest.php`](../tests/ArchTest.php) |
| CI matrix | [`.github/workflows/run-tests.yml`](../.github/workflows/run-tests.yml) |
