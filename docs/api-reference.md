# API reference

Entry points are the `Github` facade and `JordanPartridge\GithubClient\Github` from the container.

Return values are not uniform. Some methods return a Saloon response. Call `dto()` on those. Others return a DTO or an array of DTOs.

## Repositories

```php
Github::repos()->all(?int $perPage, ?int $page, ?Visibility $visibility, ?Sort $sort, ?Direction $direction, ?Type $type): Response
Github::repos()->allWithPagination(?int $perPage = 100, ?Visibility $visibility, ?Sort $sort, ?Direction $direction, ?Type $type): array
Github::repos()->get(Repo $repo): RepoData
Github::repos()->delete(Repo $repo): Response
Github::repos()->search(string $query, ?string $sort, ?Direction $order, ?int $perPage, ?int $page): SearchRepositoriesData

Github::getRepo(string $fullName): RepoData
Github::deleteRepo(string $fullName): Response
```

`all` hits `GET /user/repos` and needs an authenticated user. `search` hits the repository search API. `sort` on search is a string such as `stars`, not the `Sort` enum.

## Issues

```php
Github::issues()->all(...): Response
Github::issues()->forRepo(string $owner, string $repo, ...): Response
Github::issues()->allForRepo(string $owner, string $repo, ...): array
Github::issues()->get(string $owner, string $repo, int $issueNumber): IssueDTO
Github::issues()->create(string $owner, string $repo, string $title, ?string $body, ?array $assignees, ?int $milestone, ?array $labels): IssueDTO
Github::issues()->update(...): IssueDTO
Github::issues()->close(string $owner, string $repo, int $issueNumber): IssueDTO
Github::issues()->reopen(string $owner, string $repo, int $issueNumber): IssueDTO
Github::issues()->comments(string $owner, string $repo, int $issueNumber, ?int $perPage, ?int $page, ?string $since): array
Github::issues()->addComment(string $owner, string $repo, int $issueNumber, string $body): IssueCommentDTO
Github::issues()->getComment(string $owner, string $repo, int $commentId): IssueCommentDTO
Github::issues()->updateComment(string $owner, string $repo, int $commentId, string $body): IssueCommentDTO
Github::issues()->deleteComment(string $owner, string $repo, int $commentId): bool
```

Filter arguments on `all`, `forRepo`, and `allForRepo` are `perPage`, `page`, `Issues\State`, `labels` (string), `Issues\Sort`, `Direction`, `assignee`, `creator`, `mentioned`, and `since`.

`State` is `OPEN`, `CLOSED`, or `ALL`. `Sort` is `CREATED`, `UPDATED`, or `COMMENTS`.

## Pull requests

```php
Github::pullRequests()->all(string $owner, string $repo, array $parameters = []): array
Github::pullRequests()->get(string $owner, string $repo, int $number): PullRequestDTO
Github::pullRequests()->create(string $owner, string $repo, string $title, string $head, string $base, string $body = '', bool $draft = false): PullRequestDTO
Github::pullRequests()->update(string $owner, string $repo, int $number, array $parameters = []): PullRequestDTO
Github::pullRequests()->merge(string $owner, string $repo, int $number, ?string $commitMessage = null, ?string $sha = null, MergeMethod $mergeMethod = MergeMethod::Merge): bool
Github::pullRequests()->files(string $owner, string $repo, int $number): array
Github::pullRequests()->diff(string $owner, string $repo, int $number): array
Github::pullRequests()->comments(string $owner, string $repo, int $number): array
Github::pullRequests()->summaries(string $owner, string $repo, array $parameters = []): array
Github::pullRequests()->detail(string $owner, string $repo, int $number): PullRequestDetailDTO
```

`MergeMethod` is `Merge`, `Squash`, or `Rebase`.

`diff()` groups the files from `files()` into `summary`, `categories`, `files`, and `analysis_tags`. Categories are `tests`, `config`, `docs`, `code`, and `other`, based on the path and extension.

`comments()` here is the direct list. `Github::comments()->forPullRequest($number, ['repository' => 'owner/repo'])` is a filtered wrapper around the same review-comment endpoint. The wrapper throws if `repository` is missing.

Review helpers on the pull request resource: `reviews`, `createReview`, `createComment`, `commentsWithFilters`, `getComment`, `updateComment`, `deleteComment`, `detailsForMultiple`, `recentDetails`.

## Commits and files

```php
Github::commits()->all(string $repoName, ?int $perPage = 100, ?int $page = 1): array
Github::commits()->get(string $repoName, string $commitSha): CommitData

Github::files()->all(string $repoName, string $commitSha): Response
```

`$repoName` is `owner/repo`. `$commitSha` must match `^[0-9a-f]{40}$`.

The commit message is `$commit->commit->message`. The GitHub user, when present, is `$commit->author->login`.

## Releases

```php
Github::releases()->all(string $owner, string $repo, ?int $perPage, ?int $page): array
Github::releases()->get(string $owner, string $repo, int $releaseId): ReleaseData
Github::releases()->latest(string $owner, string $repo): ReleaseData
```

## Actions

```php
Github::actions()->listWorkflows(string $owner, string $repo, ?int $perPage, ?int $page): Response
Github::actions()->getWorkflowRuns(string $owner, string $repo, int $workflowId, ...): Response
Github::actions()->triggerWorkflow(string $owner, string $repo, int $workflowId, array $data): Response
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

On the default branch, and not in Packagist `v3.0.0`:

```php
Github::forInstallation(int $installationId): Github
Github::withApp(string $appId, string $privateKey, ?int $installationId = null): Github

Github::installations()->list(?int $perPage, ?int $page): array
Github::installations()->listAll(?int $perPage = 100): array
Github::installations()->get(int $installationId): InstallationData
Github::installations()->createAccessToken(int $installationId, ?array $repositories, ?array $permissions): InstallationTokenData
```

`forInstallation()` reads `github-client.github_app.app_id` and the private key from config.
