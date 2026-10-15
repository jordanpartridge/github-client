# How the client is put together

`GithubClientServiceProvider` registers four things:

- The `github-client` config file
- The `github-client` Artisan command
- A singleton `GithubConnector`
- `JordanPartridge\GithubClient\Github`, which the `Github` facade resolves

Each resource (`repos()`, `issues()`, `pullRequests()`, and the rest) builds a Saloon request, sends it through `GithubConnector`, and maps the body in `createDtoFromResponse()`.

DTOs in `src/Data` are plain PHP objects with a `fromArray()` or `fromApiResponse()` method. They are not Spatie Laravel Data objects. A missing key throws while the object is constructed. List endpoints that use `HandlesIssueResponses` skip an item that cannot be mapped.

`Repo::fromFullName('owner/repo')` checks that the string has one slash and that each side matches `[a-zA-Z0-9._-]+`.

Enums replace a few query strings: `Visibility`, `Sort`, `Direction`, `Repos\Type`, `Issues\State`, `Issues\Sort`, `Pulls\State`, `Pulls\Sort`, and `MergeMethod`. Pull request list filters are still a plain array.
