# Architecture

This document provides a comprehensive overview of the architectural design and components of the Laravel GitHub Client package.

## Overview

The GitHub Client for Laravel is built on a clean, modular architecture that follows Laravel's best practices and patterns. The package is designed to be:

- **User-friendly**: Intuitive API that follows Laravel conventions
- **Maintainable**: Clear separation of concerns and modular components
- **Performant**: Efficient HTTP communication with the GitHub API
- **Testable**: Easy to test with mock responses
- **Type-safe**: Fully typed responses and parameters

## Core Components

The architecture consists of these key components:

```text
┌─────────────────────┐      ┌───────────────────┐      ┌───────────────────┐
│    GitHub Facade    │      │  GitHub Client    │      │  Saloon Connector  │
│    (Entry Point)    │─────▶│  (Main Service)   │─────▶│  (HTTP Layer)      │
└─────────────────────┘      └───────────────────┘      └───────────────────┘
                                       │
                                       │
                                       ▼
┌─────────────────────┐      ┌───────────────────┐      ┌───────────────────┐
│   Request Classes   │◀─────│   Resources       │─────▶│   Data Transfer   │
│   (API Endpoints)   │      │   (API Entities)   │      │   Objects (DTOs)  │
└─────────────────────┘      └───────────────────┘      └───────────────────┘
```

### 1. Entry Points

- **Facades**: `Github` and `GithubOAuth` facades provide static access to the client
- **Service Container**: Direct dependency injection of `Github` or `GithubConnector`

### 2. Core Services

- **Github**: Main entry point for the API client
- **GithubConnector**: Handles HTTP communication with GitHub's API using Saloon
- **GithubOAuth**: Handles OAuth authentication with GitHub

### 3. Resources

Resources implement the Repository pattern for different GitHub entities:

- **RepoResource**: Manages repository operations
- **CommitResource**: Handles repository commits
- **PullRequestResource**: Manages pull requests, reviews, and comments
- **FileResource**: Handles file operations

Resources provide a consistent interface:
- `all()`: List all resources with filtering options
- `get()`: Retrieve a specific resource
- Additional specialized methods for specific needs

### 4. Request Classes

Each API endpoint is represented by a dedicated Request class in the `Requests` directory:

- **Index.php**: List/index endpoints
- **Get.php**: Retrieve specific resource
- **Delete.php**: Delete a resource

These classes handle:
- Endpoint resolution
- Parameter validation
- Query/body parameters

### 5. Data Transfer Objects (DTOs)

DTOs provide strongly typed structures for GitHub API responses:

- **RepoData**: Repository information
- **CommitData**: Commit information
- **PullRequestDTO**: Pull request information
- **FileDTO**: File information

DTOs are immutable objects with typed properties, leveraging spatie/laravel-data.

### 6. Value Objects

Value objects encapsulate and validate complex identifiers:

- **Repo**: Validates and normalizes repository names
- Additional value objects for other complex identifiers

### 7. Enums

PHP 8.2+ enums are used for type-safe parameter validation:

- **Visibility**: Repository visibility options (PUBLIC, PRIVATE, ALL)
- **Direction**: Sort direction (ASC, DESC)
- **Sort**: Sort fields (CREATED, UPDATED, PUSHED, FULL_NAME)
- **MergeMethod**: PR merge methods (MERGE, SQUASH, REBASE)
- **State**: PR states (OPEN, CLOSED, ALL)

## Data Flow

1. **Request Initiation**:
   ```php
   Github::repos()->get('owner/repo');
   ```

2. **Resource Handling**:
   - `RepoResource` creates a `Get` request
   - Validates parameters using value objects

3. **HTTP Communication**:
   - `GithubConnector` sends the request to GitHub's API
   - Handles authentication and headers

4. **Response Processing**:
   - Response is converted to a DTO
   - Types are properly cast (dates, nested objects)

5. **Result Delivery**:
   - Typed DTO is returned to the caller

## Error Handling

The package implements a comprehensive error handling approach:

1. **Input Validation**:
   - Type validation through PHP 8.2+ type hints
   - Value validation through value objects
   - Enum validation for allowed values

2. **HTTP Errors**:
   - GitHub API errors are caught and converted to Laravel-friendly exceptions
   - Rate limiting, authentication, and permission errors are properly handled

3. **Exception Hierarchy**:
   - `GithubAuthException`: Authentication issues
   - Additional specific exceptions for different error scenarios

## Testing Approach

The package is built with testability in mind:

1. **Mock Responses**:
   ```php
   Github::connector()->withMockClient($mockClient);
   ```

2. **Test Categories**:
   - Unit tests for value objects and validation
   - Feature tests for resources and requests
   - Integration tests for the full workflow

3. **Test Coverage**:
   - All public methods have corresponding tests
   - Both success and error scenarios are tested
   - Edge cases are addressed

## Extension Points

The architecture is designed to be extensible:

1. **New Resources**:
   - Create a new class extending `BaseResource`
   - Add it to the `GithubConnector` and `GithubConnectorInterface`

2. **New Methods**:
   - Add methods to existing resources
   - Create corresponding request classes

3. **New DTOs**:
   - Create new DTO classes in the `Data` directory
   - Use spatie/laravel-data attributes for casting

## Configuration

The package uses Laravel's configuration system:

- **github-client.php**: Main configuration file
  - API token
  - Base URL
  - OAuth settings

## Security Considerations

1. **Token Handling**:
   - Tokens are stored in environment variables
   - Token validation ensures proper format

2. **Rate Limiting**:
   - GitHub API rate limits are respected
   - Rate limit errors are properly handled

3. **Authentication**:
   - OAuth implementation follows security best practices
   - Token refresh mechanisms are properly implemented

## Performance Optimization

1. **HTTP Efficiency**:
   - Uses Saloon's response caching when appropriate
   - Pagination to handle large datasets efficiently

2. **DTO Optimization**:
   - Lazy loading of nested resources
   - Selective property mapping for efficiency