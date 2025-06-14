# Resources

This directory contains the core resource classes that handle GitHub API interactions. Each resource class represents a specific GitHub API endpoint group and follows Laravel's resource pattern for consistent interaction.

## Architecture

Resources are the cornerstone of the GitHub Client's architecture, providing a clean, consistent interface to the GitHub API. The architecture follows these principles:

1. **Resource Pattern**: Each GitHub entity has a dedicated resource class
2. **Strongly Typed**: All methods return strongly typed responses using DTOs
3. **Immutable**: All resources are declared as `readonly` to enforce immutability
4. **Consistent API**: Resources follow a common pattern with methods like `all()` and `get()`
5. **Request Encapsulation**: API requests are encapsulated in dedicated request classes
6. **Value Objects**: Complex entity identifiers use value objects for validation

## Class Structure

- `BaseResource.php` - Abstract base class that provides common functionality for all resources
- `CommitResource.php` - Handles operations related to repository commits
- `FileResource.php` - Manages file-level operations within repositories
- `PullRequestResource.php` - Handles all pull request related operations
- `RepoResource.php` - Manages repository-level operations

## Resource Pattern

All resources extend the `BaseResource` class and implement the `ResourceInterface`:

```php
readonly class SomeResource extends BaseResource
{
    public function __construct(
        private GithubConnectorInterface $connector,
    ) {}

    public function all(...): Response { ... }
    public function get(...): SomeData { ... }
    // Additional methods specific to this resource
}
```

## Standard Method Pattern

Each resource follows a consistent pattern for method naming and behavior:

```php
use JordanPartridge\GithubClient\Facades\Github;

// Common pattern across resources
$resource->all();    // List all resources (returns Response)
$resource->get();    // Get a specific resource (returns typed DTO)
$resource->create(); // Create a new resource when applicable (returns Response or DTO)
$resource->update(); // Update an existing resource when applicable (returns Response)
$resource->delete(); // Delete a resource when applicable (returns Response)
```

## Resource-Specific Methods

### RepoResource

```php
// List repositories with filtering options
$repos = Github::repos()->all(
    per_page: 30,
    page: 1,
    visibility: Visibility::PUBLIC,
    sort: Sort::CREATED,
    direction: Direction::DESC
);

// Get a specific repository (returns RepoData)
$repo = Github::repos()->get('jordanpartridge/github-client');

// Delete a repository
$response = Github::repos()->delete('jordanpartridge/github-client');
```

### CommitResource

```php
// List all commits for a repository
$commits = Github::commits()->all(
    'jordanpartridge/github-client',
    per_page: 20,
    page: 1
);

// Get a specific commit by SHA (returns CommitData)
$commit = Github::commits()->get('jordanpartridge/github-client', 'abc123deadbeef');
```

### PullRequestResource

```php
// List all pull requests
$pullRequests = Github::pullRequests()->all(
    'jordanpartridge/github-client',
    state: State::OPEN,
    sort: Sort::CREATED,
    direction: Direction::DESC
);

// Get a specific pull request (returns PullRequestDTO)
$pr = Github::pullRequests()->get('jordanpartridge/github-client', 123);

// Create a new pull request
$newPr = Github::pullRequests()->create(
    owner: 'jordanpartridge',
    repo: 'github-client',
    title: 'New feature implementation',
    head: 'feature-branch',
    base: 'main',
    body: 'This PR implements the new feature'
);

// Merge a pull request
$mergeResult = Github::pullRequests()->merge(
    owner: 'jordanpartridge',
    repo: 'github-client',
    number: 123,
    mergeMethod: MergeMethod::Squash
);

// Work with reviews
$reviews = Github::pullRequests()->reviews('jordanpartridge/github-client', 123);

// Work with comments
$comments = Github::pullRequests()->comments('jordanpartridge/github-client', 123);
```

### FileResource

```php
// Get file contents
$fileContent = Github::files()->get(
    owner: 'jordanpartridge',
    repo: 'github-client',
    path: 'README.md'
);

// List directory contents
$contents = Github::files()->contents(
    owner: 'jordanpartridge',
    repo: 'github-client',
    path: 'src'
);
```

## Data Transfer Objects (DTOs)

Each resource typically returns strongly typed DTOs for specific entities:

- `RepoResource->get()` returns a `RepoData` object
- `CommitResource->get()` returns a `CommitData` object
- `PullRequestResource->get()` returns a `PullRequestDTO` object
- `FileResource->get()` returns a `FileDTO` object

These DTOs provide type-safe access to response properties with proper typing:

```php
$repo = Github::repos()->get('jordanpartridge/github-client');

// Access typed properties
echo $repo->name;               // string
echo $repo->id;                 // int
echo $repo->created_at->format('Y-m-d'); // Carbon date
echo $repo->owner->login;       // nested object
```

## Error Handling

All resources handle errors consistently through the BaseResource class and Saloon's error handling. Common error scenarios:

- Rate limiting (429)
- Authentication issues (401)
- Not found resources (404)
- Validation errors (422)

## Testing Resources

Each resource has corresponding test cases in the `/tests` directory. When adding new resource methods, ensure proper test coverage using Saloon's MockClient:

```php
// In tests
$mockClient = new MockClient([
    '*' => MockResponse::make([
        'id' => 1,
        'name' => 'test-repo'
    ], 200),
]);

Github::connector()->withMockClient($mockClient);
```

## Creating New Resources

When adding a new GitHub API resource, follow these guidelines:

1. Create a new class that extends `BaseResource`
2. Define it as `readonly` 
3. Implement the common methods (`all()`, `get()`) where applicable
4. Create corresponding DTO classes in the `Data` directory
5. Create request classes in the `Requests` directory
6. Add the resource to the `GithubConnector` and `GithubConnectorInterface`
7. Add comprehensive PHPDoc comments with examples
8. Create tests for the new resource