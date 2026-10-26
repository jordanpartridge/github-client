# How the client is put together

This package implements a **3-Way CLI Pattern** that provides three complementary ways to interact with the GitHub API:

1. **Facade** (`Github::`) - Static access for convenience
2. **Resources** (`repos()`, `issues()`) - Grouped methods by feature area
3. **CLI Command** (`php artisan github-client`) - Command-line interface

The `Github` class is the **core package** that orchestrates all three access patterns. See [3-Way CLI Pattern](./3WAY-CLI-PATTERN.md) for a detailed explanation.

`GithubClientServiceProvider` registers four things:

- The `github-client` config file
- The `github-client` Artisan command
- A singleton `GithubConnector`
- `JordanPartridge\GithubClient\Github`, which the `Github` facade resolves

Each resource (`repos()`, `issues()`, `pullRequests()`, and the rest) builds a Saloon request, sends it through `GithubConnector`, and maps the body in `createDtoFromResponse()`.

DTOs in `src/Data` are plain PHP objects with a `fromArray()` or `fromApiResponse()` method. They are not Spatie Laravel Data objects. A missing key throws while the object is constructed. List endpoints that use `HandlesIssueResponses` skip an item that cannot be mapped.

`Repo::fromFullName('owner/repo')` checks that the string has one slash and that each side matches `[a-zA-Z0-9._-]+`.

Enums replace a few query strings: `Visibility`, `Sort`, `Direction`, `Repos\Type`, `Issues\State`, `Issues\Sort`, `Pulls\State`, `Pulls\Sort`, and `MergeMethod`. Pull request list filters are still a plain array.

## The Core Package Role

The `Github` class is the **core package** that ties all three access patterns together:

- **Resource Factory**: Creates resource instances (`repos()`, `issues()`, etc.)
- **Connector Holder**: Maintains a single `GithubConnector` for HTTP requests
- **Authentication Gateway**: Routes all authentication through `GithubConnector`
- **Rate Limit Tracking**: Provides rate limit info via `getRateLimitStatus()`
- **GitHub App Support**: Handles installation-based auth via `forInstallation()` and `withApp()`

All three ways to access the API (Facade, Resources, CLI) use the same `Github` core class.
