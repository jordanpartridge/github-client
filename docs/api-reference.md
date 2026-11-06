# API reference

Entry points are the `Github` facade and `JordanPartridge\GithubClient\Github` from the container.

Return values are not uniform. Some methods return a Saloon response; call `dto()` only when the request supplies a DTO mapper. Others return a DTO or an array of DTOs. The signatures below preserve public parameter names for PHP named arguments; they are reference notation, not executable statements. `Response` is `Saloon\Http\Response`, `Collection` is `Illuminate\Support\Collection`, and `Github` in return types is the client, not the facade.

## Repositories

```php
Github::repos()->all(?int $per_page = null, ?int $page = null, ?Visibility $visibility = null, ?Sort $sort = null, ?Direction $direction = null, ?Type $type = null): Response
Github::repos()->allWithPagination(?int $per_page = 100, ?Visibility $visibility = null, ?Sort $sort = null, ?Direction $direction = null, ?Type $type = null): array
Github::repos()->get(Repo $repo): RepoData
Github::repos()->delete(Repo $repo): Response
Github::repos()->search(string $query, ?string $sort = null, ?Direction $order = null, ?int $per_page = null, ?int $page = null): SearchRepositoriesData

Github::getRepo(string $fullName): RepoData
Github::deleteRepo(string $fullName): Response
```

`all` hits `GET /user/repos` and needs an authenticated user. `allWithPagination` returns `RepoData[]`, follows the next-page `Link` header, and caps at 1000 pages. `search` hits the repository search API. `sort` on search is a string such as `stars`, not the `Sort` enum. `Type` is `Enums\Repos\Type`; `Visibility`, `Sort`, and `Direction` are in `JordanPartridge\GithubClient\Enums`.

## Issues

```php
Github::issues()->all(?int $per_page = 100, ?int $page = null, ?State $state = null, ?string $labels = null, ?Sort $sort = null, ?Direction $direction = null, ?string $assignee = null, ?string $creator = null, ?string $mentioned = null, ?string $since = null): Response
Github::issues()->forRepo(string $owner, string $repo, ?int $per_page = 100, ?int $page = null, ?State $state = null, ?string $labels = null, ?Sort $sort = null, ?Direction $direction = null, ?string $assignee = null, ?string $creator = null, ?string $mentioned = null, ?string $since = null): Response
Github::issues()->allForRepo(string $owner, string $repo, ?int $per_page = 100, ?State $state = null, ?string $labels = null, ?Sort $sort = null, ?Direction $direction = null, ?string $assignee = null, ?string $creator = null, ?string $mentioned = null, ?string $since = null): array
Github::issues()->get(string $owner, string $repo, int $issue_number): IssueDTO
Github::issues()->create(string $owner, string $repo, string $title, ?string $body = null, ?array $assignees = null, ?int $milestone = null, ?array $labels = null): IssueDTO
Github::issues()->update(string $owner, string $repo, int $issue_number, ?string $title = null, ?string $body = null, ?State $state = null, ?array $assignees = null, ?int $milestone = null, ?array $labels = null): IssueDTO
Github::issues()->close(string $owner, string $repo, int $issue_number): IssueDTO
Github::issues()->reopen(string $owner, string $repo, int $issue_number): IssueDTO
Github::issues()->comments(string $owner, string $repo, int $issue_number, ?int $per_page = 100, ?int $page = null, ?string $since = null): array
Github::issues()->addComment(string $owner, string $repo, int $issue_number, string $body): IssueCommentDTO
Github::issues()->getComment(string $owner, string $repo, int $commentId): IssueCommentDTO
Github::issues()->updateComment(string $owner, string $repo, int $commentId, string $body): IssueCommentDTO
Github::issues()->deleteComment(string $owner, string $repo, int $commentId): bool
```

Here `State` and `Sort` are in `JordanPartridge\GithubClient\Enums\Issues`. `State` is `OPEN`, `CLOSED`, or `ALL`; `Sort` is `CREATED`, `UPDATED`, or `COMMENTS`. `labels` is a comma-separated string for list filters, but an array for create/update. `all` and `forRepo` return one page; `allForRepo` follows next-page links up to 1000 pages and returns `IssueDTO[]`. It has no `page` argument. Issue lists exclude pull requests.

## Pull requests

```php
Github::pullRequests()->all(string $owner, string $repo, array $parameters = []): array
Github::pullRequests()->paginate(string $owner, string $repo, array $parameters = []): array
Github::pullRequests()->get(string $owner, string $repo, int $number): PullRequestDTO
Github::pullRequests()->create(string $owner, string $repo, string $title, string $head, string $base, string $body = '', bool $draft = false): PullRequestDTO
Github::pullRequests()->update(string $owner, string $repo, int $number, array $parameters = []): PullRequestDTO
Github::pullRequests()->merge(string $owner, string $repo, int $number, ?string $commitMessage = null, ?string $sha = null, MergeMethod $mergeMethod = MergeMethod::Merge): bool
Github::pullRequests()->files(string $owner, string $repo, int $number): array
Github::pullRequests()->diff(string $owner, string $repo, int $number): array
Github::pullRequests()->comments(string $owner, string $repo, int $number): array
Github::pullRequests()->summaries(string $owner, string $repo, array $parameters = []): array
Github::pullRequests()->detail(string $owner, string $repo, int $number): PullRequestDetailDTO
Github::pullRequests()->detailsForMultiple(string $owner, string $repo, array $prNumbers, int $maxRequests = 20): array
Github::pullRequests()->recentDetails(string $owner, string $repo, int $limit = 5, string $state = 'open'): array
```

`MergeMethod` is `Merge`, `Squash`, or `Rebase`.

`all()` returns one page of `PullRequestDTO` objects. `paginate()` starts at page 1, follows next-page `Link` headers, stops on an empty page, and caps at 1000 pages. List parameters are `state`, `head`, `base`, `sort`, `direction`, `per_page`, and `page`; pass strings for state/sort/direction, not enum objects.

```php
$prs = Github::pullRequests()->paginate('owner', 'repo', [
    'state' => 'open',
    'per_page' => 100,
]);

$summaries = Github::pullRequests()->summaries('owner', 'repo');
if ($summaries !== []) {
    $detail = Github::pullRequests()->detail('owner', 'repo', $summaries[0]->number);
}
```

`summaries()` returns one page of `PullRequestSummaryDTO` objects without detailed statistics. `detail()` returns counts and change statistics from the individual PR endpoint. `all()` maps missing statistics to zero, so those zeros do not establish actual comment or change counts.

`detailsForMultiple()` makes one detail request per supplied PR number, truncates to `$maxRequests` (default 20), and skips caught failures. `recentDetails()` makes one summary-list request (at most 10 entries), then one detail request per selected PR; its default limit is 5. Both return `PullRequestDetailDTO[]`. These are sequential API calls, not a single batch endpoint.

`diff()` groups the files from `files()` into `summary`, `categories`, `files`, and `analysis_tags`. Categories are `tests`, `config`, `docs`, `code`, and `other`, based on the path and extension.

### Reviews and review comments

```php
Github::pullRequests()->reviews(string $owner, string $repo, int $number): Collection
Github::pullRequests()->createReview(string $owner, string $repo, int $number, string $body, string $event = 'COMMENT', array $comments = []): PullRequestReviewDTO
Github::pullRequests()->createComment(string $owner, string $repo, int $number, string $body, string $commitId, string $path, int $position): PullRequestCommentDTO
Github::pullRequests()->commentsWithFilters(string $owner, string $repo, int $number, array $filters = []): array
Github::pullRequests()->forPullRequest(string $owner, string $repo, int $number, array $filters = []): array
Github::pullRequests()->getComment(string $owner, string $repo, int $commentId): PullRequestCommentDTO
Github::pullRequests()->updateComment(string $owner, string $repo, int $commentId, string $body): PullRequestCommentDTO
Github::pullRequests()->deleteComment(string $owner, string $repo, int $commentId): bool
```

`reviews()` returns a collection of `PullRequestReviewDTO` objects. `comments()` returns one page of inline review comments; `forPullRequest()` on this resource aliases `commentsWithFilters()`. To write a conversation comment on a PR instead of an inline review comment, use `issues()->addComment($owner, $repo, $number, $body)`.

```php
$review = Github::pullRequests()->createReview(
    owner: 'owner',
    repo: 'repo',
    number: 42,
    body: 'Looks good to me.',
    event: 'APPROVE',
);
```

### Filtered comments resource

```php
Github::comments()->forPullRequest(int $prNumber, array $filters = []): array
Github::comments()->byAuthor(int $prNumber, string $author, array $additionalFilters = []): array
Github::comments()->byAuthorType(int $prNumber, string $authorType, array $additionalFilters = []): array
Github::comments()->bySeverity(int $prNumber, string $severity, array $additionalFilters = []): array
Github::comments()->forFile(int $prNumber, string $filePath, array $additionalFilters = []): array
Github::comments()->codeRabbit(int $prNumber, array $additionalFilters = []): array
Github::comments()->bots(int $prNumber, array $additionalFilters = []): array
Github::comments()->humans(int $prNumber, array $additionalFilters = []): array
```

These helpers return `PullRequestCommentDTO[]` and require `'repository' => 'owner/repo'` in the filters; there is no default repository. `codeRabbit`, `bots`, and `humans` are author/author-type shortcuts.

```php
$comments = Github::comments()->forPullRequest(42, [
    'repository' => 'owner/repo',
    'author_type' => 'bot',
    'contains' => 'security',
    'per_page' => 100,
    'page' => 1,
]);
```

Filters are `author`, `author_type` (`bot` or `human`, inferred from login patterns), `severity`, `file_path`, `since`, `until`, `claim_type`, and `contains`. Severity and claim type depend on parsed comment metadata. Filtering happens **locally after fetching one API page**; only `per_page` (capped at 100) and `page` are sent to GitHub. There is no automatic pagination, so an empty filtered page does not prove later pages have no matches. The same filtering rules apply to `pullRequests()->commentsWithFilters()`, which receives owner/repo separately.

## Commits and files

```php
Github::commits()->all(string $repo_name, ?int $per_page = 100, ?int $page = 1): array
Github::commits()->get(string $repo_name, string $commit_sha)

Github::files()->all(string $repo_name, string $commit_sha): Response
```

`$repo_name` is `owner/repo`. `$commit_sha` must be a 40-character hexadecimal SHA (case-insensitive). `commits()->all()` returns one page of `CommitData` objects; `commits()->get()` returns `CommitData` but has no declared PHP return type.

The commit message is `$commit->commit->message`. The GitHub user, when present, is `$commit->author->login`.

`files()->all()` requests `GET /repos/{owner}/{repo}/commits/{sha}` and returns its raw Saloon response: a **commit payload, not a Git tree**. Read changed files with `$response->json('files')`. For typed `CommitFileData` objects, use `commits()->get($repo_name, $commit_sha)->files` instead. Neither call promises to fetch additional pages of changed files automatically.

## Releases

```php
Github::releases()->all(string $owner, string $repo, ?int $per_page = null, ?int $page = null): array
Github::releases()->get(string $owner, string $repo, int $releaseId): ReleaseData
Github::releases()->latest(string $owner, string $repo): ReleaseData
```

## Actions

```php
Github::actions()->listWorkflows(string $owner, string $repo, ?int $per_page = null, ?int $page = null): Response
Github::actions()->getWorkflowRuns(string $owner, string $repo, int $workflow_id, ?int $per_page = null, ?int $page = null, ?string $status = null, ?string $conclusion = null, ?string $branch = null): Response
Github::actions()->triggerWorkflow(string $owner, string $repo, int $workflow_id, array $data = []): Response
```

`$data['ref']` is required. `$data['inputs']` must be an array when it is set.

## Rate limit

```php
Github::getRateLimitStatus(): array
Github::getRateLimitForResource(string $resource = 'core'): RateLimitDTO
Github::hasRateLimitExceeded(): bool
```

`hasRateLimitExceeded()` returns `false` when the rate-limit request itself throws.

## GitHub App

Available from `v3.1.0`:

```php
Github::forInstallation(int $installationId): self
Github::withApp(string $appId, string $privateKey, ?int $installationId = null): self

Github::installations()->list(?int $per_page = null, ?int $page = null): array
Github::installations()->listAll(?int $per_page = 100): array
Github::installations()->get(int $installationId): InstallationData
Github::installations()->createAccessToken(int $installationId, ?array $repositories = null, ?array $permissions = null): InstallationTokenData
```

Here `self` is `JordanPartridge\GithubClient\Github`. `forInstallation()` reads `github-client.github_app.app_id` and the private key from config, returning an installation-authenticated client. `withApp()` accepts PEM contents directly, not a key path. Omit its installation id for app-level JWT authentication when calling `installations()`; supply the id for repository operations as that installation. `list()` returns one page of `InstallationData`; `listAll()` follows next-page links up to 100 pages. See [authentication](./advanced-usage.md#github-app) for examples and config key paths.
