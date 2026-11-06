# GitHub Client for Laravel

A Laravel package for GitHub's REST API. It uses [Saloon](https://docs.saloon.dev) for HTTP, maps responses onto PHP objects, and exposes repositories, issues, pull requests, commits, releases, and Actions from a facade or the container.

```bash
composer require jordanpartridge/github-client
```

[![Tests](https://img.shields.io/github/actions/workflow/status/shadetheory/github-client/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/shadetheory/github-client/actions/workflows/run-tests.yml)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/shadetheory/github-client/phpstan.yml?branch=master&label=phpstan&style=flat-square)](https://github.com/shadetheory/github-client/actions/workflows/phpstan.yml)
[![Latest Version](https://img.shields.io/packagist/v/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)
[![Downloads](https://img.shields.io/packagist/dt/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)
[![License](https://img.shields.io/github/license/shadetheory/github-client?style=flat-square)](LICENSE.md)

`Github::forInstallation()`, `Github::withApp()`, and `installations()` are available from `v3.1.0`.

See the [documentation index](docs/README.md) for the API reference, authentication details, and architecture.

## Install

```bash
composer require jordanpartridge/github-client
```

Publish the config file:

```bash
php artisan vendor:publish --tag=github-client-config
```

## Configure

The package reads a token from config. The published file maps that value from the environment.

```env
GITHUB_TOKEN=ghp_your_token_here
```

`GH_TOKEN` is also read, through `github-client.gh_token`, when `GITHUB_TOKEN` is empty.

Resolution order in `TokenResolver`:

1. `config('github-client.token')`
2. `config('github-client.gh_token')`
3. `gh auth token`, when the GitHub CLI is installed and authenticated

If none of those is set, requests go out without a token. Public repositories still respond. Unauthenticated calls use GitHub's lower rate limit.

Create a token at [github.com/settings/tokens](https://github.com/settings/tokens). Listing the authenticated user's repositories needs a token. Creating issues and pull requests needs a token that can write to the repository.

OAuth and GitHub App settings are in the same config file. See [OAuth](#oauth) and [GitHub App](#github-app).

## Usage

```php
use JordanPartridge\GithubClient\Facades\Github;

$repos = Github::repos()->all()->dto();

$repo = Github::getRepo('jordanpartridge/github-client');

echo $repo->name;
echo $repo->stargazers_count;
echo $repo->owner->login;
```

`repos()->all()` returns a Saloon response. `dto()` turns the body into a list of `RepoData` objects. `Github::getRepo()` accepts an `owner/repo` string, validates it, and returns one `RepoData`.

`repos()->get()` takes a `Repo` value object, not a string:

```php
use JordanPartridge\GithubClient\ValueObjects\Repo;

$repo = Github::repos()->get(Repo::fromFullName('jordanpartridge/github-client'));
```

`repos()->delete()` takes the same value object and returns the Saloon response.

### Pagination

`all()` requests one page (GitHub's default is 30). `allWithPagination()` follows the `Link` header and returns every `RepoData` it can fetch, up to 1000 pages.

```php
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;

$repos = Github::repos()->allWithPagination(
    visibility: Visibility::PUBLIC,
    sort: Sort::CREATED,
    direction: Direction::DESC,
);
```

### Issues

```php
use JordanPartridge\GithubClient\Enums\Issues\State;

$issue = Github::issues()->create(
    owner: 'jordanpartridge',
    repo: 'github-client',
    title: 'Bug: Something broke',
    body: 'Here are the details...',
    labels: ['bug'],
    assignees: ['jordanpartridge'],
);

echo $issue->number;
echo $issue->html_url;

$bugs = Github::issues()->forRepo(
    owner: 'jordanpartridge',
    repo: 'github-client',
    state: State::OPEN,
    labels: 'bug',
)->dto();

$comments = Github::issues()->comments('jordanpartridge', 'github-client', $issue->number);

Github::issues()->addComment('jordanpartridge', 'github-client', $issue->number, 'Fixed in the latest release.');

Github::issues()->close('jordanpartridge', 'github-client', $issue->number);
```

`forRepo()` and `all()` return a Saloon response. Call `dto()` for `IssueDTO` objects. `allForRepo()` paginates the same way `allWithPagination()` does and returns the array itself.

GitHub's issue list includes pull requests. List endpoints skip items that have a `pull_request` key.

`labels` on `forRepo()` is a comma-separated string. `labels` on `create()` is a list of label names.

### Pull requests

```php
use JordanPartridge\GithubClient\Enums\MergeMethod;

$pr = Github::pullRequests()->create(
    owner: 'jordanpartridge',
    repo: 'github-client',
    title: 'Add a feature',
    head: 'feature-branch',
    base: 'main',
    body: 'This pull request adds the feature.',
    draft: false,
);

$merged = Github::pullRequests()->merge(
    owner: 'jordanpartridge',
    repo: 'github-client',
    number: $pr->number,
    mergeMethod: MergeMethod::Squash,
);
```

`merge()` returns `true` when GitHub reports the pull request as merged.

`pullRequests()->all($owner, $repo)` returns `PullRequestDTO` objects. Pass GitHub query parameters in the third argument, for example `['state' => 'open']`. `files($owner, $repo, $number)` returns the changed files. There is no `commits()` method on the pull request resource. Use `commits()` for commit history.

`all()` returns one page. To follow next-page links (up to 1000 pages), use:

```php
$prs = Github::pullRequests()->paginate('owner', 'repo', [
    'state' => 'open',
    'per_page' => 100,
]);
```

Use `summaries()` for one page of `PullRequestSummaryDTO` objects without detailed statistics, and `detail()` for one PR's counts and change statistics. Missing statistics in `all()` default to zero; they are not actual counts. `detailsForMultiple()` fetches one detail per PR (default cap 20), skipping caught failures. `recentDetails()` makes one list request plus one detail request per selected PR (default 5, list capped at 10).

`reviews()` and `createReview()` read and submit reviews. `createComment()` creates an inline review comment with a commit id, path, and diff position; `issues()->addComment()` writes a conversation comment, including on a PR. Filter review comments with an explicit repository:

```php
$comments = Github::comments()->bots(42, [
    'repository' => 'owner/repo',
    'contains' => 'security',
    'per_page' => 100,
    'page' => 1,
]);
```

Filtering is local to **one fetched API page**, not an automatic scan of every comment. An empty filtered page does not mean later pages have no matches. See the [review and comment reference](docs/api-reference.md#reviews-and-review-comments) for signatures and all supported filters.

### Commits, releases, files, Actions

```php
$commits = Github::commits()->all('laravel/framework');

$commit = Github::commits()->get(
    'laravel/framework',
    '0123456789abcdef0123456789abcdef01234567',
);

echo $commit->sha;
echo $commit->commit->message;
```

`commits()->get()` and `files()->all()` require a 40 character hexadecimal SHA.

```php
$latest = Github::releases()->latest('laravel', 'framework');

echo $latest->tag_name;
```

`releases()` can list releases and fetch one by id. It cannot create a release.

```php
$response = Github::files()->all(
    'laravel/framework',
    '0123456789abcdef0123456789abcdef01234567',
);

$files = $response->json('files');
```

Replace the example SHA with a real commit SHA from the repository. `files()->all()` returns a raw Saloon response containing the commit payload, **not a Git tree**. Read its changed files with `json('files')`; `commits()->get(...)->files` is the typed `CommitFileData` alternative. Neither automatically fetches additional pages of changed files. There is no `get()` or `contents()` method on the file resource.

```php
$workflows = Github::actions()->listWorkflows('laravel', 'framework');

$runs = Github::actions()->getWorkflowRuns('laravel', 'framework', $workflowId);

Github::actions()->triggerWorkflow('laravel', 'framework', $workflowId, [
    'ref' => 'main',
]);
```

`ref` is required. `listWorkflows()` and `getWorkflowRuns()` return Saloon responses. Owner and repo are separate arguments.

### Rate limit

```php
$limits = Github::getRateLimitStatus();
$core = Github::getRateLimitForResource('core');
```

The package does not retry rate-limited calls. A rate limit response throws `RateLimitException`.

## Resources

| Call | Methods | Returns |
| --- | --- | --- |
| `repos()` | `all`, `allWithPagination`, `get(Repo)`, `delete(Repo)`, `search` | `all` is a Saloon response. `allWithPagination` returns `RepoData[]`; `get` returns `RepoData`. `search` returns `SearchRepositoriesData`. |
| `issues()` | `all`, `forRepo`, `allForRepo`, `get`, `create`, `update`, `close`, `reopen`, `comments`, `addComment`, `getComment`, `updateComment`, `deleteComment` | `all` and `forRepo` are Saloon responses. `get`, `create`, `update`, `close`, and `reopen` return `IssueDTO`. |
| `pullRequests()` | `all`, `paginate`, `get`, `create`, `update`, `merge`, `files`, `diff`, review/comment helpers, `summaries`, `detail`, `detailsForMultiple`, `recentDetails` | `all` and `paginate` return `PullRequestDTO[]`; `get` returns `PullRequestDTO`. `merge` returns a boolean. See the [reference](docs/api-reference.md#pull-requests) for review and detail return types. |
| `commits()` | `all(owner/repo)`, `get(owner/repo, sha)` | `all` returns `CommitData[]`; `get` returns `CommitData`. |
| `files()` | `all(owner/repo, sha)` | Raw Saloon response containing a commit payload; use `json('files')`. |
| `releases()` | `all`, `get`, `latest` | `all` returns `ReleaseData[]`; `get` and `latest` return `ReleaseData`. |
| `actions()` | `listWorkflows`, `getWorkflowRuns`, `triggerWorkflow` | Saloon response |
| `comments()` | `forPullRequest`, `byAuthor`, `byAuthorType`, `bySeverity`, `forFile`, `codeRabbit`, `bots`, `humans` | Pull request review comments, filtered locally from one page. Pass `'repository' => 'owner/repo'` in the filters. `pullRequests()->comments($owner, $repo, $number)` is the direct call. |
| `installations()` | `list`, `listAll`, `get`, `createAccessToken` | GitHub App installation data. Requires app authentication. |

`Github::getRepo('owner/repo')` and `Github::deleteRepo('owner/repo')` validate the name and call the repository resource.

## Dependency injection

The container binds `JordanPartridge\GithubClient\Github`. There is no connector interface.

```php
use JordanPartridge\GithubClient\Github;

class RepoListing
{
    public function __construct(
        private readonly Github $github,
    ) {}

    public function handle(): array
    {
        return $this->github->repos()->all()->dto();
    }
}
```

## OAuth

Set these before resolving the `GithubOAuth` facade:

```env
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret
GITHUB_REDIRECT_URL=https://example.com/auth/github/callback
```

```php
use JordanPartridge\GithubClient\Connectors\GithubConnector;
use JordanPartridge\GithubClient\Facades\GithubOAuth;

$state = bin2hex(random_bytes(16));

return redirect(GithubOAuth::getAuthorizationUrl(['repo', 'user'], $state));
```

Store `$state` in the session and compare it on the callback. The package does not store or check `state`. If you omit it, `getAuthorizationUrl()` adds a random value and discards it.

```php
$token = GithubOAuth::getAccessToken($request->string('code'));

$github = new GithubConnector($token);
$repos = $github->send(
    new \JordanPartridge\GithubClient\Requests\Repos\Index(),
);
```

To keep using the facade-style resources with that token, build a client around the connector:

```php
$github = new \JordanPartridge\GithubClient\Github(new GithubConnector($token));

$repos = $github->repos()->all()->dto();
```

Missing OAuth config throws `AuthenticationException` from `getAuthorizationUrl()` and `getAccessToken()`.

## GitHub App

Available from `v3.1.0`.

```env
GITHUB_APP_ID=123456
GITHUB_APP_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----\n...\n-----END RSA PRIVATE KEY-----"
```

`GITHUB_APP_PRIVATE_KEY_PATH` is used when `GITHUB_APP_PRIVATE_KEY` is empty. The path may be absolute or relative to the Laravel base path.

```php
use JordanPartridge\GithubClient\Facades\Github;

$github = Github::forInstallation(12345678);

$repo = $github->getRepo('owner/repo');
```

Use a repository accessible to the installation. `Github::withApp($appId, $privateKey, $installationId)` creates an installation client without reading config; pass PEM contents as `$privateKey`, not a path. For example, load them with `file_get_contents($path)`.

Installation management needs **app-level JWT authentication**, not an installation token. Omit the installation id for those calls:

```php
$app = Github::withApp($appId, $privateKey);
$installations = $app->installations()->list();
$token = $app->installations()->createAccessToken(12345678);
```

`forInstallation($id)` and `withApp(..., $id)` instead use installation tokens for repository operations, refreshing them before expiry. `repos()->all()` targets the authenticated-user endpoint `/user/repos`; it does not list installation repositories.

## Artisan

```bash
php artisan github-client test
php artisan github-client repo --repo=laravel/framework
php artisan github-client commits --repo=laravel/framework --limit=10
```

`test` calls `repos()->all(per_page: 1)`. `repo` prints fields from `RepoData`. `commits` prints the SHA, author, date, and message.

## Testing

Mock the connector with Saloon. The test suite blocks unmocked Saloon network requests; no GitHub credentials are needed. `IssueDTO` reads a full issue payload, including `user`. A short fixture will throw while mapping. `tests/TestCase.php` builds a complete one, and `tests/DocumentationExamplesTest.php` covers README examples.

```php
use JordanPartridge\GithubClient\Facades\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('creates an issue', function () {
    Github::connector()->withMockClient(new MockClient([
        '*' => MockResponse::make([
            'id' => 1,
            'number' => 42,
            'title' => 'Test Issue',
            'body' => 'Details',
            'state' => 'open',
            'comments' => 0,
            'html_url' => 'https://github.com/owner/repo/issues/42',
            'user' => [
                'login' => 'owner',
                'id' => 1,
                'node_id' => 'MDQ6VXNlcjE=',
                'avatar_url' => 'https://github.com/owner.png',
                'url' => 'https://api.github.com/users/owner',
                'html_url' => 'https://github.com/owner',
                'followers_url' => 'https://api.github.com/users/owner/followers',
                'following_url' => 'https://api.github.com/users/owner/following{/other_user}',
                'gists_url' => 'https://api.github.com/users/owner/gists{/gist_id}',
                'starred_url' => 'https://api.github.com/users/owner/starred{/owner}{/repo}',
                'subscriptions_url' => 'https://api.github.com/users/owner/subscriptions',
                'organizations_url' => 'https://api.github.com/users/owner/orgs',
                'repos_url' => 'https://api.github.com/users/owner/repos',
                'events_url' => 'https://api.github.com/users/owner/events{/privacy}',
                'received_events_url' => 'https://api.github.com/users/owner/received_events',
                'type' => 'User',
            ],
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'closed_at' => null,
        ], 201),
    ]));

    $issue = Github::issues()->create('owner', 'repo', 'Test Issue');

    expect($issue->number)->toBe(42);
    expect($issue->title)->toBe('Test Issue');
});
```

For contributor setup and focused test commands, see [Contributing](#contributing). Run the full suite with:

```bash
composer test
```

`composer analyse` runs PHPStan. `vendor/bin/pint --test` checks formatting without changing files; `composer format` applies Pint fixes.

## Errors

Failed responses are mapped in `GithubConnector`:

| Status | Exception |
| --- | --- |
| 401 | `AuthenticationException` |
| 403 rate limit | `RateLimitException` |
| 403 permissions | `ApiException` |
| 404 | `ResourceNotFoundException` |
| 422 | `ValidationException` |
| 429 | `RateLimitException` |
| 500, 502, 503, 504 | `NetworkException` |

## Requirements

- PHP 8.2, 8.3, or 8.4
- Laravel 11, 12, or 13 (`illuminate/contracts`)
- `saloonphp/saloon` ^4.3
- `firebase/php-jwt` ^7.0, used by GitHub App authentication
- `spatie/laravel-package-tools` ^1.16 or ^2.0

## Contributing

Pull requests are welcome. In a checkout, install dependencies and run checks:

```bash
composer install
composer test
composer test -- --testsuite=Unit
composer test -- --testsuite=Package
composer test -- --filter=DocumentationExamplesTest
composer test -- --profile
composer analyse
vendor/bin/pint --test
```

Unit tests run without Testbench; package tests boot Laravel. Use `--filter=...` to select a test name or class and `--profile` to inspect timings. Saloon requests must be mocked, and tests need no GitHub credentials.

In an Amp orb, `.agents/setup` installs PHP 8.2 and Composer and restores cached dependencies (or installs them when no cache is available).

## License

MIT. See [LICENSE.md](LICENSE.md).

Built with [Saloon](https://github.com/saloonphp/saloon) by [Jordan Partridge](https://github.com/shadetheory).
