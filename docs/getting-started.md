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
use JordanPartridge\GithubClient\Facades\GitHub;

// List repositories
$repos = GitHub::repos()->all();

// Get specific repository
$repo = GitHub::repos()->get('owner/repo');
```

### Working with Commits

```php
// List commits
$commits = GitHub::commits()->all('owner/repo');

// Get specific commit
$commit = GitHub::commits()->get('sha');
```

## Next Steps

- Learn about [Core Concepts](./core-concepts.md)
- Explore [Advanced Usage](./advanced-usage.md)
- Review [Security Best Practices](./security.md)
