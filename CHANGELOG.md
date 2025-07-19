# Changelog

All notable changes to `github-client` will be documented in this file.

## Unreleased

## v2.5.0 - Complete GitHub Issues API - 2025-01-19

### Major New Features

#### Complete GitHub Issues API Implementation

* **Full CRUD Operations**: Create, read, update, and close issues
* **Issue Comments**: Add, list, and manage issue comments
* **Auto-Pagination**: Seamlessly paginate through large issue lists
* **Advanced Filtering**: Filter by state, labels, assignee, creator, and more
* **Defensive Programming**: Robust error handling and null-safe operations

#### New Classes and Components

**Request Classes:**
* `Get` - Retrieve individual issues by number
* `Index` - List user issues across all repositories  
* `RepoIndex` - List issues for specific repositories
* `Create` - Create new issues with full metadata
* `Update` - Update existing issues (title, body, state, etc.)
* `Comments` - List issue comments
* `CreateComment` - Add comments to issues

**Data Transfer Objects:**
* `IssueDTO` - Complete issue data representation
* `IssueCommentDTO` - Issue comment data structure
* `LabelDTO` - Issue label information

**Enums:**
* `Issues\State` - Issue states (open, closed, all)
* `Issues\Sort` - Sort options (created, updated, comments)

**Utilities:**
* `HandlesIssueResponses` - Trait for consistent response processing
* Enhanced `PullRequestDTO` with defensive programming

#### Key Features

```php
// List user issues
$issues = GitHub::issues()->index(
    state: State::OPEN,
    labels: 'bug,enhancement',
    sort: Sort::CREATED,
    direction: Direction::DESC
);

// Get specific issue
$issue = GitHub::issues()->get('owner', 'repo', 42);

// Create new issue
$newIssue = GitHub::issues()->create('owner', 'repo', 'Bug Report', 
    bodyText: 'Detailed description...',
    labels: ['bug', 'priority-high'],
    assignees: ['username']
);

// Add comment
$comment = GitHub::issues()->createComment('owner', 'repo', 42, 'Thanks for reporting!');

// Auto-pagination support
$allIssues = GitHub::issues()->index()->collect();
```

#### Quality Improvements

* **Comprehensive Testing**: 17 new test cases covering all functionality
* **Parameter Validation**: Client-side validation for issue numbers and content
* **Pull Request Filtering**: Automatically filters out PRs from issue listings
* **Error Handling**: Graceful handling of incomplete API responses
* **Documentation**: Full PHPDoc coverage for all public methods

#### Bug Fixes

* **PullRequestDTO Defensive Programming**: Fixed missing null coalescing operators for optional fields
* **Issue/PR Separation**: Proper filtering since GitHub's Issues API returns both
* **Empty Response Handling**: Robust handling of malformed or incomplete API data

## v2.4.0 - Auto-Pagination & Enhanced PullRequest Operations - 2025-01-19

### Auto-Pagination Enhancements
* Enhanced auto-pagination functionality for repositories
* Improved pagination handling with better error management
* Seamless collection of large datasets

### PullRequest Operations Improvements  
* Improved PullRequest operations with better error handling
* Enhanced data validation and processing
* More robust API response handling

### Laravel 12 Support

* Added full Laravel 12 compatibility
* Simplified dependency structure to avoid conflicts across Laravel versions
* Updated PHP requirements to support PHP 8.2, 8.3, and 8.4
* Updated development dependencies and streamlined test matrix
* Ensured backward compatibility with Laravel 10 and 11
* Added support for Pest 3.0 while maintaining compatibility with Pest 2.x
* Updated PHPStan configuration for Laravel 12 compatibility

## v0.2.1 - Commit by Sha added - 2024-11-26

### Release v0.2.1: Individual Commit Access

#### New Features

* Added support for retrieving individual commits by SHA

```php
GitHub::commits()->get('sha-hash');

```
#### Details

The new commit endpoint provides authenticated access to detailed commit information including:

- Commit metadata (author, committer, dates)
- Full commit message
- File changes and patches
- Verification information
- Associated repository data
- Parent commit references

#### Usage Example

```php
$commit = GitHub::commits()->get('abc123def456...');
// Access commit data
$author = $commit->author;
$message = $commit->message;
$changes = $commit->files;

```
#### Pull Requests

* [#10](https://github.com/jordanpartridge/github-client/pull/10) - Add individual commit retrieval by @jordanpartridge

**Full Changelog**: https://github.com/jordanpartridge/github-client/compare/v0.2...v0.2.1

## v0.2 Repo Dto - 2024-11-25

### What's Changed

* feature/commits by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/8
* repo uses dto by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/9

**Full Changelog**: https://github.com/jordanpartridge/github-client/compare/v0.1a...v0.2

## 0.1a Repository built out ready for alpha testing - 2024-11-02

### What's Changed

* repos implemented by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/1
* fix phpstan by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/4
* add visability enum by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/5
* Refactor repo by @jordanpartridge in https://github.com/jordanpartridge/github-client/pull/7

### New Contributors

* @jordanpartridge made their first contribution in https://github.com/jordanpartridge/github-client/pull/1

**Full Changelog**: https://github.com/jordanpartridge/github-client/commits/0.1a
