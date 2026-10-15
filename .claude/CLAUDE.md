# CLAUDE.md

Notes for working in this repository.

## What this package is

A Laravel package that calls the GitHub REST API with Saloon. Resources live in `src/Resources` and build request classes in `src/Requests`. Responses are mapped by `createDtoFromResponse()` onto plain PHP objects in `src/Data`. Those objects are not Spatie Laravel Data objects.

The facade `JordanPartridge\GithubClient\Facades\Github` resolves `JordanPartridge\GithubClient\Github`. Inject that class. There is no `GithubConnectorInterface`.

The connector is `JordanPartridge\GithubClient\Connectors\GithubConnector`. Its base URL is `https://api.github.com`.

## Commands

```bash
composer test
composer analyse
composer format
```

Tests are Pest. HTTP is faked with Saloon's `MockClient`. `GITHUB_TOKEN` is set in `tests/Pest.php` so token resolution does not call the GitHub CLI.

## Public API shape

- `Github::repos()->all()` returns a Saloon response. Call `dto()`.
- `Github::repos()->get()` takes `Repo::fromFullName('owner/repo')`.
- `Github::getRepo('owner/repo')` is the string helper.
- `Github::issues()->forRepo()` returns a Saloon response. `dto()` drops items that include a `pull_request` key.
- `Github::pullRequests()->merge()` returns a boolean.
- `Github::commits()->get($repo, $sha)` and `Github::files()->all($repo, $sha)` require a 40 character SHA.
- `Github::files()` has `all` only.
- `Github::releases()` has `all`, `get`, and `latest`.
- `Github::actions()` methods are `listWorkflows`, `getWorkflowRuns`, and `triggerWorkflow`.
- Token order is `github-client.token`, then `github-client.gh_token`, then `gh auth token`.

`Github::forInstallation()`, `Github::withApp()`, and `installations()` are on the default branch. They are not in the Packagist `v3.0.0` tag.

## Tests and docs

README examples are exercised in `tests/DocumentationExamplesTest.php`. If a signature changes, update that test and the README together.
