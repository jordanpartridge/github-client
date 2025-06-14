# Getting Started

## Installation

Install the package via Composer:

```bash
composer require jordanpartridge/github-client
```

## Basic Configuration

1. Generate a GitHub token in your [GitHub Settings](https://github.com/settings/tokens)
2. Add the token to your `.env` file:

```dotenv
GITHUB_TOKEN=your-token-here
```

## Basic Usage

### Working with Repositories

```php
use JordanPartridge\GithubClient\Facades\Github;

// List repositories
$repos = Github::repos()->all();

// Get specific repository
$repo = Github::repos()->get('owner/repo');
```

### Working with Commits

```php
// List commits
$commits = Github::commits()->all('owner/repo');

// Get specific commit
$commit = Github::commits()->get('sha');
```

## Next Steps

- Learn about [Core Concepts](./core-concepts.md)
- Explore [Advanced Usage](./advanced-usage.md)
- Review [Security Best Practices](./security.md)
