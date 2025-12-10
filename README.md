# GitHub Client for Laravel

**Stop wrestling with GitHub's API. Start shipping.**

```bash
composer require jordanpartridge/github-client
```

```php
// That's it. You're done.
$repos = Github::repos()->all();
$issues = Github::issues()->forRepo('owner', 'repo');
$pr = Github::pullRequests()->create('owner', 'repo', 'My PR', 'feature', 'main');
```

[![Tests](https://img.shields.io/github/actions/workflow/status/jordanpartridge/github-client/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jordanpartridge/github-client/actions)
[![Latest Version](https://img.shields.io/packagist/v/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)
[![Downloads](https://img.shields.io/packagist/dt/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)

---

## Why This Package?

- **Laravel Native** - Built for Laravel, not wrapped around it
- **Typed Responses** - DTOs everywhere, not arrays
- **Auto-Pagination** - `allWithPagination()` just works
- **Type-Safe Params** - Enums, not magic strings
- **Easy Testing** - Saloon MockClient built in

**One line: Modern GitHub API for modern Laravel.**

---

## Quick Start

### 1. Install

```bash
composer require jordanpartridge/github-client
```

### 2. Configure

Add your token to `.env`:

```env
GITHUB_TOKEN=ghp_your_token_here
```

Get one at [github.com/settings/tokens](https://github.com/settings/tokens)

### 3. Use

```php
use JordanPartridge\GithubClient\Facades\Github;

// Get your repos
$repos = Github::repos()->all();

// Get ALL your repos (auto-pagination, no limits)
$allRepos = Github::repos()->allWithPagination();

// Get a specific repo
$repo = Github::repos()->get('jordanpartridge/github-client');

echo $repo->name;              // "github-client"
echo $repo->stargazers_count;  // 🤞
echo $repo->owner->login;      // "jordanpartridge"
```

---

## Real Examples

### Create an Issue

```php
$issue = Github::issues()->create(
    owner: 'jordanpartridge',
    repo: 'github-client',
    title: 'Bug: Something broke',
    body: 'Here are the details...',
    labels: ['bug', 'high-priority'],
    assignees: ['jordanpartridge']
);

echo $issue->number;   // 42
echo $issue->html_url; // Direct link to GitHub
```

### Create a Pull Request

```php
use JordanPartridge\GithubClient\Enums\MergeMethod;

// Create PR
$pr = Github::pullRequests()->create(
    owner: 'jordanpartridge',
    repo: 'github-client',
    title: 'Add new feature',
    head: 'feature-branch',
    base: 'main',
    body: 'This PR adds the thing.',
    draft: false
);

// Merge it
Github::pullRequests()->merge(
    owner: 'jordanpartridge',
    repo: 'github-client',
    number: $pr->number,
    mergeMethod: MergeMethod::Squash
);
```

### Work with Issue Comments

```php
// Get all comments on an issue
$comments = Github::issues()->comments('owner', 'repo', 42);

// Add a comment
Github::issues()->addComment('owner', 'repo', 42, 'Fixed in latest release.');

// Close the issue
Github::issues()->close('owner', 'repo', 42);
```

### Filter with Enums (Type-Safe)

```php
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Issues\State;

// Only public repos, sorted by creation date
$repos = Github::repos()->allWithPagination(
    visibility: Visibility::PUBLIC,
    sort: Sort::CREATED,
    direction: Direction::DESC
);

// Open bugs only
$bugs = Github::issues()->forRepo(
    owner: 'jordanpartridge',
    repo: 'github-client',
    state: State::OPEN,
    labels: 'bug'
);
```

---

## Testing Your App

Saloon's MockClient makes testing trivial:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use JordanPartridge\GithubClient\Facades\Github;

it('creates issues', function () {
    $mock = new MockClient([
        '*' => MockResponse::make([
            'id' => 1,
            'number' => 42,
            'title' => 'Test Issue',
            'state' => 'open',
        ], 201),
    ]);

    Github::connector()->withMockClient($mock);

    $issue = Github::issues()->create('owner', 'repo', 'Test Issue');

    expect($issue->number)->toBe(42);
    expect($issue->title)->toBe('Test Issue');
});
```

No HTTP calls. No flaky tests. No rate limits in CI.

---

## Available Resources

| Resource | Methods |
|----------|---------|
| `repos()` | `all`, `allWithPagination`, `get`, `delete`, `search` |
| `issues()` | `all`, `forRepo`, `allForRepo`, `get`, `create`, `update`, `close`, `reopen`, `comments`, `addComment` |
| `pullRequests()` | `all`, `get`, `create`, `merge`, `files`, `commits` |
| `commits()` | `all`, `get` |
| `files()` | `get`, `contents` |
| `releases()` | `all`, `get`, `latest`, `create` |
| `actions()` | `workflows`, `runs`, `trigger` |

---

## Dependency Injection

Don't like facades? Use DI:

```php
use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;

class MyService
{
    public function __construct(
        private readonly GithubConnectorInterface $github
    ) {}

    public function getMyRepos()
    {
        return $this->github->repos()->all();
    }
}
```

---

## OAuth Flow

Building a GitHub app? OAuth is built in:

```php
use JordanPartridge\GithubClient\Facades\GithubOAuth;

// 1. Redirect user to GitHub
return redirect(GithubOAuth::getAuthorizationUrl(['repo', 'user']));

// 2. Handle callback
$token = GithubOAuth::getAccessToken($request->code);

// 3. Use their token
$github = new GithubConnector($token);
$theirRepos = $github->repos()->all();
```

---

## Requirements

- PHP 8.2+
- Laravel 11 or 12

---

## Contributing

PRs welcome. Run tests first:

```bash
composer test
```

---

## License

MIT. Go build something.

---

**Built with [Saloon](https://github.com/Sammyjo20/Saloon)** by [Jordan Partridge](https://github.com/jordanpartridge)
