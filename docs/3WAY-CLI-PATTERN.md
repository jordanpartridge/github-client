# 3-Way CLI Pattern

This package implements a **3-Way CLI Pattern** that provides three complementary ways to interact with the GitHub API:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        3-Way CLI Pattern                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│  1. Facade (Github::)    2. Resources (repos(), issues())  3. CLI Command │
│     Static Access            Method Grouping                Artisan Command │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           Core Package Role                                │
│                        The Github Class                                    │
│  Orchestrates all three access patterns through a single, consistent API   │
└─────────────────────────────────────────────────────────────────────────────┘
```

## The Three Ways

### Way 1: Facade Access (`Github::`)

Use the `Github` facade for static, convenient access to the API:

```php
use JordanPartridge\GithubClient\Facades\Github;

// List repositories
$repos = Github::repos()->all()->dto();

// Get a specific repository
$repo = Github::getRepo('laravel/framework');
```

**Use when**: You want the simplest, most direct access to the API without dependency injection.

### Way 2: Resource Methods (`repos()`, `issues()`)

Access the API through resource-specific methods that group related functionality:

```php
use JordanPartridge\GithubClient\Facades\Github;

// Repository resource
$repos = Github::repos()->all()->dto();
$repo = Github::repos()->get($repo);
Github::repos()->delete($repo);

// Issue resource
$issue = Github::issues()->create($owner, $repo, $title, $body);

// Pull request resource
$pullRequests = Github::pullRequests()->all($owner, $repo);
```

**Use when**: You want organized access to related API endpoints, or when injecting the client for testability.

### Way 3: CLI Command (`php artisan github-client`)

Use Artisan commands for quick inspections and testing from the command line:

```bash
# Test the GitHub API connection
php artisan github-client test

# Show repository information
php artisan github-client repo --repo=laravel/framework

# Show recent commits
php artisan github-client commits --repo=laravel/framework --limit=10
```

**Use when**: You need quick access to GitHub data without writing PHP code, or for debugging.

## The Core Package Role

The `Github` class is the **core package** that ties all three access methods together:

```php
namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Connectors\GithubConnector;
use JordanPartridge\GithubClient\Resources\RepoResource;
use JordanPartridge\GithubClient\Resources\IssuesResource;
use JordanPartridge\GithubClient\Resources\PullRequestResource;
// ... all other resources

class Github
{
    public function __construct(
        protected GithubConnector $connector,
    ) {}

    // Resource methods - connect to resources
    public function repos(): RepoResource
    {
        return new RepoResource($this);
    }

    public function issues(): IssuesResource
    {
        return new IssuesResource($this);
    }

    public function pullRequests(): PullRequestResource
    {
        return new PullRequestResource($this);
    }

    // ... all other resource methods
}
```

### Core Responsibilities

1. **Resource Factory**: Creates resource instances (`repos()`, `issues()`, etc.)

2. **Connector Holder**: Maintains a single `GithubConnector` instance for HTTP requests

3. **Authentication Gateway**: Routes authentication through `GithubConnector`

4. **Rate Limit Tracking**: Provides rate limit information via `getRateLimitStatus()`

5. **GitHub App Support**: Handles installation-based authentication via `forInstallation()` and `withApp()`

### How the Core Ties Everything Together

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Facade     │     │  Resources   │     │     CLI      │
│ Github::     │     │ repos(),     │     │ artisan      │
│ static calls │────▶│ issues()     │────▶│ github-client│
└──────────────┘     │ pullRequests()│    └──────────────┘
                     └──────────────┘
                            │
                            ▼
                    ┌──────────────┐
                    │   Core       │
                    │   Github     │
                    │   class      │
                    └──────────────┘
                            │
                            ▼
                    ┌──────────────┐
                    │  Connector   │
                    │  (HTTP)      │
                    └──────────────┘
                            │
                            ▼
                    ┌──────────────┐
                    │ GitHub API   │
                    └──────────────┘
```

## Choosing the Right Pattern

| Scenario | Recommended Pattern |
|----------|---------------------|
| Quick script or quick test |_facade (`Github::`) |
| Class-based architecture |_Inject `Github` class |
| Related endpoint group | Use resource (`repos()`) |
| CI/CD scripts or shell automation | CLI command |
| Debugging | CLI command |

## Example: All Three Ways to List Repositories

```php
// Way 1: Facade
use JordanPartridge\GithubClient\Facades\Github;
$repos = Github::repos()->all()->dto();

// Way 2: Resource
$repos = Github::repos()->all()->dto();

// Way 3: CLI
// php artisan github-client list-repos
```

All three ways use the same underlying core package and return the same results.
