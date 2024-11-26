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
- Modern PHP 8.1+ codebase
- Laravel-style resource pattern

## Installation

Install the package via Composer:

```bash
composer require jordanpartridge/github-client
```

## Configuration

1. Generate a GitHub token in your [GitHub Settings](https://github.com/settings/tokens)
2. Add the token to your `.env` file:

```
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
```

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
