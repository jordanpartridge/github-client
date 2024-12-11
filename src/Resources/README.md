# Resources

This directory contains the core resource classes that handle GitHub API interactions. Each resource class represents a specific GitHub API endpoint group and follows Laravel's resource pattern for consistent interaction.

## Overview

- `BaseResource.php` - Abstract base class that provides common functionality for all resources
- `CommitResource.php` - Handles operations related to repository commits
- `FileResource.php` - Manages file-level operations within repositories
- `PullRequestResource.php` - Handles all pull request related operations
- `RepoResource.php` - Manages repository-level operations

## Usage Pattern

Each resource follows a consistent pattern for method naming and behavior:

```php
use JordanPartridge\GithubClient\Facades\GitHub;

// Basic pattern
$resource->all();    // List all resources
$resource->get();    // Get a specific resource
$resource->create(); // Create a new resource (when applicable)
$resource->update(); // Update an existing resource (when applicable)
$resource->delete(); // Delete a resource (when applicable)
```

## Resource-Specific Methods

### CommitResource
```php
GitHub::commits()->all('owner/repo');          // List all commits
GitHub::commits()->get('owner/repo', 'sha');   // Get specific commit
```

### FileResource
```php
GitHub::files()->get('owner/repo', 'path');    // Get file contents
GitHub::files()->create(...);                  // Create/update file
```

### PullRequestResource
```php
GitHub::pullRequests()->all('owner/repo');     // List all PRs
GitHub::pullRequests()->get('owner/repo', 1);  // Get specific PR
GitHub::pullRequests()->create(...);           // Create new PR
GitHub::pullRequests()->merge(...);            // Merge PR
```

### RepoResource
```php
GitHub::repos()->all();                        // List user repos
GitHub::repos()->get('owner/repo');            // Get specific repo
GitHub::repos()->create(...);                  // Create new repo
```

## Error Handling

All resources handle errors consistently through the BaseResource class. Common error scenarios:

- Rate limiting (429)
- Authentication issues (401)
- Not found resources (404)
- Validation errors (422)

## Testing

Each resource has corresponding test cases in the `/tests/Resources` directory. When adding new resource methods, ensure proper test coverage.