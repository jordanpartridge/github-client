# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Laravel package providing a strongly-typed GitHub API client built on Saloon. This package integrates with Laravel's service container and configuration system while providing resource-based access to GitHub's API endpoints.

## Key Commands

### Testing
```bash
# Run all tests
vendor/bin/pest

# Run specific test file
vendor/bin/pest tests/Feature/ReposTest.php

# Run with coverage
vendor/bin/pest --coverage

# Run tests matching pattern
vendor/bin/pest --filter="it can get a repository"
```

### Code Quality
```bash
# Format code with Laravel Pint
vendor/bin/pint

# Check formatting without fixing
vendor/bin/pint --test

# Run static analysis
vendor/bin/phpstan analyse
```

### Development
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Run tests in CI environment
GITHUB_TOKEN=dummy-token-for-testing vendor/bin/pest
```

## Architecture

### Core Components

**Resource Pattern**: All API endpoints are organized as resources extending `BaseResource`:
- Resources live in `src/Resources/` and handle high-level API operations
- Each resource maps to a GitHub API entity (repos, pulls, issues, etc.)
- Resources use request classes in `src/Requests/` for actual API calls
- Resources return strongly-typed DTOs from `src/Data/`

**Request/Response Flow**:
1. User calls method on resource (e.g., `Github::repos()->get()`)
2. Resource creates appropriate request object from `src/Requests/`
3. Request is sent via Saloon connector (`ConduitUi\GitHubConnector\GithubConnector`)
4. Response is transformed into DTO using `spatie/laravel-data`
5. Typed DTO is returned to user

**Authentication System**:
- Uses `ConduitUi\GitHubConnector\GithubConnector` with token authentication
- Token configured via `GITHUB_TOKEN` environment variable
- OAuth support available through `GithubOAuth` class for user authentication flows

### Directory Structure

```
src/
├── Auth/                  # Authentication strategies
├── Commands/              # Artisan commands
├── Concerns/              # Shared traits
├── Contracts/             # Interfaces
├── Data/                  # DTOs for API responses
│   ├── Commits/
│   ├── Issues/
│   ├── Pulls/
│   ├── Releases/
│   └── Repos/
├── Enums/                 # Type-safe enums for API parameters
├── Exceptions/            # Custom exception classes
├── Facades/               # Laravel facades
├── Requests/              # Saloon request classes
│   ├── Actions/
│   ├── Commits/
│   ├── Files/
│   ├── Issues/
│   ├── Pulls/
│   ├── Releases/
│   └── Repos/
├── Resources/             # Resource classes for API entities
└── ValueObjects/          # Value objects (e.g., Repo)
```

### Key Design Patterns

**Value Objects**: The `Repo` value object validates and parses repository identifiers:
- `Repo::fromFullName("owner/repo")` - Creates from full name
- `Repo::fromUrl("https://github.com/owner/repo")` - Creates from URL
- Provides `owner()` and `name()` accessors

**Enum-Based Validation**: All API parameters use PHP enums for type safety:
- `Visibility` (PUBLIC, PRIVATE, ALL)
- `Sort` (CREATED, UPDATED, PUSHED, FULL_NAME)
- `Direction` (ASC, DESC)
- `MergeMethod` (MERGE, SQUASH, REBASE)
- Issue/PR specific enums in their respective namespaces

**DTO Pattern**: All API responses are mapped to DTOs using `spatie/laravel-data`:
- Provides type safety and IDE autocompletion
- Handles nullable fields gracefully
- Supports nested data structures

## Testing Approach

Tests use Pest PHP with Laravel testing helpers. Key patterns:
- Mock Saloon responses using `MockClient`
- Test both success and failure scenarios
- Validate DTO structure and data transformation
- Test enum validation and parameter handling

## Known Issues & TODOs

### ReleasesResource Bug
The `ReleasesResource` class incorrectly calls `Repo::fromOwnerAndRepo()` which doesn't exist. Should use `Repo::fromFullName()` instead. Affected lines: ~77, ~106, ~122

### Authentication Enhancement Needed
- Make authentication optional for public repositories
- Support GitHub CLI token reuse (`gh auth token`)
- Better error messages for missing tokens
- Check authentication sources in order: GitHub CLI → env vars → config

### Missing Helper Method
Add `Repo::fromOwnerAndRepo(string $owner, string $repo)` convenience method that internally calls `fromFullName()`.

## Integration Points

### Laravel Service Provider
`GithubClientServiceProvider` registers:
- GitHub facade binding
- Configuration publishing
- Service container bindings

### Configuration
Published via `config/github-client.php`:
- Token configuration
- OAuth settings
- API endpoint customization

### Facade Usage
```php
use JordanPartridge\GithubClient\Facades\Github;

$repos = Github::repos()->all();
```

### Dependency Injection
```php
use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;

public function __construct(GithubConnectorInterface $github) {
    $this->github = $github;
}
```

## Error Handling

Custom exceptions provide context-specific error information:
- `ApiException` - API request failures
- `AuthenticationException` - Auth issues
- `RateLimitException` - Rate limit exceeded
- `ValidationException` - Input validation failures
- `NetworkException` - Connectivity issues

## Performance Considerations

- Auto-pagination available via `allWithPagination()` methods
- Rate limit checking via `getRateLimitStatus()`
- Response caching handled by Saloon
- Lazy loading of resources to minimize memory usage