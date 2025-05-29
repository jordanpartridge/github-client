# GitHub Client for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jordanpartridge/github-client/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jordanpartridge/github-client/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jordanpartridge/github-client/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jordanpartridge/github-client/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)

A powerful, Laravel-first GitHub API client built on Saloon that makes integrating with GitHub's API simple and intuitive.

## Features

- Built on Saloon for reliable API handling in Laravel
- Full type-hinting support with typed responses
- Seamless integration with Laravel's configuration and authentication
- Comprehensive test coverage
- Support for facades and dependency injection
- Modern PHP 8.2+ codebase
- Support for Laravel 10, 11, and 12
- Laravel-style resource pattern

## Installation

Install the package via Composer:

```bash
composer require jordanpartridge/github-client
```

## Configuration

1. Generate a GitHub token in your [GitHub Settings](https://github.com/settings/tokens)
2. Add the token to your `.env` file:

```dotenv
GITHUB_TOKEN=your-token-here
```

## Usage

### Laravel-Style Resource Pattern

This package follows a Laravel-inspired resource pattern for intuitive API interaction:

```php
use JordanPartridge\GithubClient\Facades\GitHub;

// Working with Repositories
$repos = GitHub::repos(); // Get the repository resource
$allRepos = $repos->all(); // Get all repositories
$specificRepo = $repos->get('jordanpartridge/github-client'); // Get specific repository

// Working with Commits
$commits = GitHub::commits()->all('jordanpartridge/github-client'); // Get all commits for a repository
$specificCommit = GitHub::commits()->get('abc123...'); // Get a specific commit by SHA

// Working with Pull Requests
$prs = GitHub::pullRequests()->all('owner/repo'); // Get all pull requests
$specificPr = GitHub::pullRequests()->get('owner/repo', 123); // Get specific pull request
```

Each resource follows a consistent pattern similar to Laravel's basic resource operations:
- `all()` - Retrieve all resources
- `get()` - Retrieve a specific resource

### Available Resources

```php
// Repositories
GitHub::repos()->all(); // List all repositories
GitHub::repos()->get('owner/repo'); // Get a specific repository

// Commits
GitHub::commits()->all('owner/repo'); // List all commits for a repository
GitHub::commits()->get('sha'); // Get a specific commit by SHA

// Pull Requests
GitHub::pullRequests()->all('owner/repo'); // List all pull requests
GitHub::pullRequests()->get('owner/repo', 123); // Get a specific pull request
GitHub::pullRequests()->create('owner/repo', 'Title', 'feature-branch', 'main', 'Description'); // Create a pull request
GitHub::pullRequests()->merge('owner/repo', 123, 'Merge message', null, MergeMethod::Squash); // Merge a pull request

// Pull Request Reviews
GitHub::pullRequests()->reviews('owner/repo', 123); // List reviews
GitHub::pullRequests()->createReview('owner/repo', 123, 'LGTM!', 'APPROVE'); // Create a review

// Pull Request Comments
GitHub::pullRequests()->comments('owner/repo', 123); // List comments
GitHub::pullRequests()->createComment('owner/repo', 123, 'Nice work!', 'commit-sha', 'path/to/file.php', 5); // Create a comment

// GitHub Actions
GitHub::actions()->listWorkflows('owner/repo'); // List workflows
GitHub::actions()->getWorkflowRuns('owner/repo', 123); // Get workflow runs
GitHub::actions()->triggerWorkflow('owner/repo', 123, ['ref' => 'main', 'inputs' => ['key' => 'value']]); // Trigger workflow
```

### Working with Pull Requests

The package provides comprehensive support for working with GitHub Pull Requests:

```php
use JordanPartridge\GithubClient\Facades\GitHub;
use JordanPartridge\GithubClient\Enums\MergeMethod;

// List pull requests
$pullRequests = GitHub::pullRequests()->all('owner/repo');

// Create a pull request
$pullRequest = GitHub::pullRequests()->create(
    owner: 'owner',
    repo: 'repo',
    title: 'New Feature',
    head: 'feature-branch',
    base: 'main',
    body: 'This PR adds a new feature',
    draft: false
);

// Get a specific pull request
$pullRequest = GitHub::pullRequests()->get('owner', 'repo', 123);

// Update a pull request
$updated = GitHub::pullRequests()->update('owner', 'repo', 123, [
    'title' => 'Updated Title',
    'body' => 'Updated description',
]);

// Merge a pull request
$merged = GitHub::pullRequests()->merge(
    owner: 'owner',
    repo: 'repo',
    number: 123,
    commitMessage: 'Merging new feature',
    mergeMethod: MergeMethod::Squash
);

// Work with reviews
$reviews = GitHub::pullRequests()->reviews('owner', 'repo', 123);
$review = GitHub::pullRequests()->createReview(
    owner: 'owner',
    repo: 'repo',
    number: 123,
    body: 'Looks good!',
    event: 'APPROVE'
);

// Work with comments
$comments = GitHub::pullRequests()->comments('owner', 'repo', 123);
$comment = GitHub::pullRequests()->createComment(
    owner: 'owner',
    repo: 'repo',
    number: 123,
    body: 'Consider this approach',
    commitId: 'abc123',
    path: 'src/File.php',
    position: 5
);
```

All responses are properly typed using data transfer objects (DTOs) powered by spatie/laravel-data:
- `PullRequestDTO`
- `PullRequestReviewDTO`
- `PullRequestCommentDTO`

### Working with GitHub Actions

The package provides comprehensive support for GitHub Actions workflows:

```php
use JordanPartridge\GithubClient\Facades\GitHub;

// List workflows for a repository
$workflows = GitHub::actions()->listWorkflows('owner', 'repo');

// Get workflow runs for a specific workflow
$runs = GitHub::actions()->getWorkflowRuns(
    owner: 'owner',
    repo: 'repo', 
    workflow_id: 161335,
    per_page: 50,
    status: 'completed',
    conclusion: 'success',
    branch: 'main'
);

// Trigger a workflow dispatch event
$result = GitHub::actions()->triggerWorkflow(
    owner: 'owner',
    repo: 'repo',
    workflow_id: 161335,
    data: [
        'ref' => 'main',
        'inputs' => [
            'environment' => 'production',
            'debug' => 'false'
        ]
    ]
);

// List all workflows with pagination
$workflows = GitHub::actions()->listWorkflows(
    owner: 'owner',
    repo: 'repo',
    per_page: 30,
    page: 2
);
```

#### Available Actions Operations

- **List Workflows**: Get all workflows for a repository
- **Get Workflow Runs**: Retrieve runs for a specific workflow with filtering options
- **Trigger Workflow**: Dispatch a workflow_dispatch event with custom inputs

#### Filtering Options

When getting workflow runs, you can filter by:
- `status`: completed, action_required, cancelled, failure, neutral, skipped, stale, success, timed_out, in_progress, queued, requested, waiting
- `conclusion`: action_required, cancelled, failure, neutral, success, skipped, stale, timed_out
- `branch`: Filter by branch name
- Standard pagination with `per_page` and `page`

### Using Dependency Injection

```php
use JordanPartridge\GithubClient\Contracts\GitHub;

public function __construct(
    private readonly GitHub $github
) {}
```

### Custom Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="github-client-config"
```

## Documentation

For detailed documentation, please visit our [documentation page](https://github.com/jordanpartridge/github-client#documentation).

## Testing

Run the test suite:

```bash
composer test
```

## Contributing

Contributions are welcome! Please:

1. Add tests for new functionality
2. Follow PSR-12 coding standards
3. Submit a Pull Request with a clear description of changes

## License

This package is open-source software licensed under the MIT license.

## Credits

- [Jordan Partridge](https://github.com/jordanpartridge)
- [All Contributors](../../contributors)

Built with Saloon and Laravel
