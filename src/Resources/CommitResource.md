- Error handling
# CommitResource

The CommitResource provides access to GitHub's Git commit API endpoints. It allows you to fetch both individual commits and lists of commits for a repository.

## Methods

### all(string $repo_name, ?int $per_page = 100, ?int $page = 1): array

Fetches all commits for a given repository with pagination support.

```php
use JordanPartridge\GithubClient\Facades\GitHub;

// Get first 100 commits
$commits = GitHub::commits()->all('owner/repo');

// Get specific page with custom page size
$commits = GitHub::commits()->all('owner/repo', per_page: 50, page: 2);
```

Parameters:
- `$repo_name`: Full repository name (e.g., 'owner/repo')
- `$per_page`: Number of commits per page (default: 100)
- `$page`: Page number to fetch (default: 1)

### get(string $repo_name, string $commit_sha)

Fetches a specific commit by its SHA.

```php
$commit = GitHub::commits()->get('owner/repo', '123abc');
```

Parameters:
- `$repo_name`: Full repository name (e.g., 'owner/repo')
- `$commit_sha`: The commit SHA

## Error Handling

The resource validates repository names and will throw:
- `InvalidArgumentException` for invalid repository names
- `RequestException` for API errors (404 for non-existent repos/commits)

## Testing

The CommitResource has comprehensive test coverage in `tests/CommitResourceTest.php`. Tests cover:

- Fetching all commits with pagination
- Fetching specific commits by SHA
- Repository name validation
- Error handling

When adding new methods, ensure to:
1. Add corresponding test cases
2. Test both success and failure scenarios
3. Verify pagination if applicable
4. Test input validation