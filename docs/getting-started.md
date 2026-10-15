# Getting started

## Install

```bash
composer require jordanpartridge/github-client
php artisan vendor:publish --tag=github-client-config
```

That Composer command installs the latest Packagist tag. The calls on this page match the default branch.

## Token

```env
GITHUB_TOKEN=ghp_your_token_here
```

`TokenResolver` checks `config('github-client.token')`, then `config('github-client.gh_token')`, then `gh auth token`. With no token, public repository requests still run, on GitHub's unauthenticated rate limit.

## First calls

```php
use JordanPartridge\GithubClient\Facades\Github;

$repos = Github::repos()->all()->dto();

$repo = Github::getRepo('laravel/framework');

$commits = Github::commits()->all('laravel/framework');
```

`repos()->all()` returns a Saloon response, so call `dto()` before reading `RepoData` properties. `repos()->get()` does not accept a string. Pass `Repo::fromFullName('owner/repo')`, or use `Github::getRepo()`.

`commits()->get($repo, $sha)` needs the full `owner/repo` name and a 40 character SHA.

## Next

- [API reference](./api-reference.md)
- [Authentication and errors](./advanced-usage.md)
