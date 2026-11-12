# Resources

Each class in this directory is one GitHub API area. `Github` builds them and they send Saloon requests through `GithubConnector`.

Resources are `readonly` and extend `BaseResource`. The constructor receives the `Github` client, not a connector interface.

Methods do not share one return type. `RepoResource::all()` and `IssuesResource::forRepo()` return a Saloon response (`dto()` maps the body). `RepoResource::get()` returns `RepoData` and takes a `Repo` value object. `IssuesResource::get()` takes owner, repo, and issue number and returns `IssueDTO`.

```php
use ShadeTheory\GithubClient\Facades\Github;
use ShadeTheory\GithubClient\ValueObjects\Repo;

$repos = Github::repos()->all()->dto();

$repo = Github::repos()->get(Repo::fromFullName('laravel/framework'));

$issue = Github::issues()->get('laravel', 'framework', 1);

$pulls = Github::pullRequests()->all('laravel', 'framework');
```

`FileResource` has `all($repo_name, $commit_sha)` and returns a raw commit response, not a Git tree. Read its changed files with `$response->json('files')`. `ReleasesResource` has `all`, `get`, and `latest`. `ActionsResource` has `listWorkflows`, `getWorkflowRuns`, and `triggerWorkflow`, each with separate owner and repo arguments.

The method list that matches the code is in [docs/api-reference.md](../../docs/api-reference.md).
