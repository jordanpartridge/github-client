# GitHub Client for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jordanpartridge/github-client/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jordanpartridge/github-client/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jordanpartridge/github-client/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jordanpartridge/github-client/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)
[![PHP Version](https://img.shields.io/packagist/php-v/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2B%2C%2012%2B-red?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)

A powerful, Laravel-first GitHub API client built on Saloon that makes integrating with GitHub's API simple and intuitive. This package provides strongly-typed responses, resource-based architecture, and an elegant developer experience.

## 🌟 Features

- **Elegant Resource Pattern**: A Laravel-style resource pattern for all GitHub API entities
- **Strongly Typed**: Full type-hinting support with typed responses via DTOs
- **Built on [Saloon](https://github.com/Sammyjo20/Saloon)**: Reliable API handling with MockClient for testing
- **Comprehensive Coverage**: Support for repositories, commits, pull requests, issues, and files
- **Laravel Integration**: Seamless integration with Laravel's configuration and authentication
- **Multiple Access Methods**: Support for facades and dependency injection
- **Modern Codebase**: PHP 8.2+ with modern features like enums and readonly properties
- **Extensive Test Coverage**: Complete test suite using Pest PHP
- **Laravel Compatibility**: Support for Laravel 11 and 12

## 📦 Installation

Install the package via Composer:

```bash
composer require jordanpartridge/github-client
```

## ⚙️ Configuration

1. Generate a GitHub token in your [GitHub Settings](https://github.com/settings/tokens) with appropriate scopes
2. Add the token to your `.env` file:

```dotenv
GITHUB_TOKEN=your-token-here
```

3. (Optional) Publish the configuration file if you need custom settings:

```bash
php artisan vendor:publish --tag="github-client-config"
```

## 📄 Available Resources

This package provides the following GitHub resource classes:

| Resource | Description |
|----------|-------------|
| `repos()` | Access and manage GitHub repositories |
| `commits()` | Work with repository commits |
| `pullRequests()` | Manage pull requests, reviews, and comments |
| `issues()` | Manage GitHub issues, comments, and labels |
| `files()` | Access repository file contents |

## 🚀 Basic Usage

### Working with Repositories

```php
use JordanPartridge\GithubClient\Facades\Github;

// List repositories (first page only, max 30 by default)
$firstPageRepos = Github::repos()->all();

// List ALL repositories with automatic pagination
$allRepos = Github::repos()->allWithPagination();

// Get a specific repository
$repo = Github::repos()->get('jordanpartridge/github-client');

// Filter repositories by visibility
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Direction;

$publicRepos = Github::repos()->all(
    visibility: Visibility::PUBLIC,
);

// Get ALL public repositories with auto-pagination
$allPublicRepos = Github::repos()->allWithPagination(
    visibility: Visibility::PUBLIC,
    sort: Sort::CREATED,
    direction: Direction::DESC
);
```

### Working with Commits

```php
// Get all commits for a repository
$commits = Github::commits()->all('jordanpartridge/github-client');

// Get a specific commit by SHA
$specificCommit = Github::commits()->get('jordanpartridge/github-client', 'abc123deadbeef');
```

### Working with Pull Requests

```php
use JordanPartridge\GithubClient\Enums\MergeMethod;

// List all pull requests for a repository
$pullRequests = Github::pullRequests()->all('jordanpartridge/github-client');

// Get a specific pull request
$pullRequest = Github::pullRequests()->get('jordanpartridge/github-client', 123);

// Create a new pull request
$newPr = Github::pullRequests()->create(
    owner: 'jordanpartridge',
    repo: 'github-client',
    title: 'New feature implementation',
    head: 'feature-branch',
    base: 'main',
    body: 'This PR implements the new feature with tests',
    draft: false
);

// Merge a pull request
$mergeResult = Github::pullRequests()->merge(
    owner: 'jordanpartridge',
    repo: 'github-client',
    number: 123,
    commitMessage: 'Implement new feature',
    mergeMethod: MergeMethod::Squash
);
```

### Working with Issues

```php
use JordanPartridge\GithubClient\Enums\Issues\State;
use JordanPartridge\GithubClient\Enums\Issues\Sort;

// List issues across all your repositories
$myIssues = Github::issues()->all();

// List issues for a specific repository
$repoIssues = Github::issues()->forRepo('jordanpartridge', 'github-client');

// Get ALL issues with auto-pagination
$allIssues = Github::issues()->allForRepo('jordanpartridge', 'github-client');

// Filter issues by state, labels, and assignee
$openBugs = Github::issues()->forRepo(
    owner: 'jordanpartridge',
    repo: 'github-client', 
    state: State::OPEN,
    labels: 'bug,high-priority',
    sort: Sort::CREATED,
    direction: Direction::DESC
);

// Get a specific issue
$issue = Github::issues()->get('jordanpartridge', 'github-client', 123);

// Create a new issue
$newIssue = Github::issues()->create(
    owner: 'jordanpartridge',
    repo: 'github-client',
    title: 'Bug: Auto-pagination not working',
    body: 'Steps to reproduce...',
    labels: ['bug', 'high-priority'],
    assignees: ['maintainer-username']
);

// Update an issue
$updatedIssue = Github::issues()->update(
    owner: 'jordanpartridge',
    repo: 'github-client',
    issue_number: 123,
    title: 'Updated title',
    state: State::CLOSED
);

// Close/reopen issues
$closedIssue = Github::issues()->close('jordanpartridge', 'github-client', 123);
$reopenedIssue = Github::issues()->reopen('jordanpartridge', 'github-client', 123);

// Work with issue comments
$comments = Github::issues()->comments('jordanpartridge', 'github-client', 123);
$newComment = Github::issues()->addComment(
    'jordanpartridge', 
    'github-client', 
    123, 
    'This has been fixed in the latest release.'
);
```

### Working with Repository Files

```php
// Get file contents from a repository
$fileContent = Github::files()->get(
    owner: 'jordanpartridge',
    repo: 'github-client',
    path: 'README.md'
);

// List directory contents
$files = Github::files()->contents(
    owner: 'jordanpartridge',
    repo: 'github-client',
    path: 'src'
);
```

### Using Dependency Injection

You can use dependency injection instead of the Facade:

```php
use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;

class GitHubService
{
    public function __construct(
        private readonly GithubConnectorInterface $github
    ) {}
    
    public function getRepositories()
    {
        return $this->github->repos()->all();
    }
}
```

## 🔄 Type-Safe API Responses

All responses are properly typed using data transfer objects (DTOs) powered by [spatie/laravel-data](https://github.com/spatie/laravel-data):

```php
// $repo is a strongly-typed RepoData object
$repo = Github::repos()->get('jordanpartridge/github-client');

// Access typed properties
echo $repo->name;
echo $repo->full_name;
echo $repo->created_at->format('Y-m-d');
echo $repo->owner->login;
```

## 🧪 Testing

When testing your application, you can use Saloon's MockClient to mock GitHub API responses:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use JordanPartridge\GithubClient\Facades\Github;

// Set up mock response
$mockClient = new MockClient([
    '*' => MockResponse::make([
        'id' => 1,
        'name' => 'test-repo',
        'full_name' => 'test/test-repo',
    ], 200),
]);

// Apply mock client to GitHub connector
Github::connector()->withMockClient($mockClient);

// Now any API calls will return the mock response
$repo = Github::repos()->get('any/repo');
```

## 📖 Advanced Documentation

### Parameter Type Validation

This package uses PHP 8.2+ enums for parameter validation, ensuring you always pass valid values:

```php
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\MergeMethod;
use JordanPartridge\GithubClient\Enums\Pulls\State;
use JordanPartridge\GithubClient\Enums\Issues\State as IssueState;
use JordanPartridge\GithubClient\Enums\Issues\Sort as IssueSort;

// Using enums for parameter validation
$repos = Github::repos()->all(
    visibility: Visibility::PUBLIC,    // 'public', 'private', 'all'
    sort: Sort::CREATED,               // 'created', 'updated', 'pushed', 'full_name'
    direction: Direction::DESC         // 'asc', 'desc'
);

// Pull request states
$openPrs = Github::pullRequests()->all(
    'jordanpartridge/github-client',
    state: State::OPEN                 // 'open', 'closed', 'all'
);

// Merge methods
$mergeResult = Github::pullRequests()->merge(
    'jordanpartridge',
    'github-client',
    123,
    mergeMethod: MergeMethod::Squash   // 'merge', 'squash', 'rebase'
);

// Issue states and sorting
$issues = Github::issues()->forRepo(
    'jordanpartridge',
    'github-client',
    state: IssueState::OPEN,           // 'open', 'closed', 'all'
    sort: IssueSort::CREATED           // 'created', 'updated', 'comments'
);
```

### OAuth Authentication

For web applications that need user authentication:

```php
use JordanPartridge\GithubClient\Facades\GithubOAuth;

// Get authorization URL
$authUrl = GithubOAuth::getAuthorizationUrl([
    'repo',           // Access repositories
    'user',           // Access user profile
    'read:org',       // Read organization access
]);

// Redirect user to authorization URL
return redirect()->away($authUrl);

// In your callback route
$token = GithubOAuth::getAccessToken($code);

// Store the token for the authenticated user
auth()->user()->update(['github_token' => $token]);

// Use the user's token for GitHub API requests
$github = new \JordanPartridge\GithubClient\GithubConnector($token);
$userRepos = $github->repos()->all();
```

## 🔧 Local Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run a specific test
vendor/bin/pest tests/ReposTest.php

# Run static analysis
composer analyse

# Format code
composer format
```

## 📜 License

This package is open-source software licensed under the [MIT license](LICENSE.md).

## ✨ Credits

- [Jordan Partridge](https://github.com/jordanpartridge)
- [All Contributors](../../contributors)

Built with [Saloon](https://github.com/Sammyjo20/Saloon) and [Laravel](https://laravel.com)