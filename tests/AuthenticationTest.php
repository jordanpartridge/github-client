<?php

use Illuminate\Support\Facades\Process;
use JordanPartridge\GithubClient\Auth\TokenResolver;
use JordanPartridge\GithubClient\Connectors\GithubConnector;
use JordanPartridge\GithubClient\Facades\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use JordanPartridge\GithubClient\Data\Repos\RepoData;

describe('Authentication improvements', function () {
    it('allows unauthenticated requests for public repositories', function () {
        $connector = new GithubConnector('');

        expect($connector->isAuthenticated())->toBeFalse();
    });

    it('resolves tokens in documented priority order', function (?string $primary, ?string $fallback, string $expected, string $source) {
        config(['github-client.token' => $primary, 'github-client.gh_token' => $fallback]);
        Process::fake(fn ($process) => Process::result(output: match ($process->command) {
            'which gh' => '/usr/bin/gh',
            'gh auth token' => "cli-token\n",
            default => '',
        }));

        expect(TokenResolver::resolve())->toBe($expected)
            ->and(TokenResolver::getLastSource())->toBe($source);
    })->with([
        'primary config wins' => ['primary-token', 'fallback-token', 'primary-token', 'config'],
        'GH_TOKEN fallback' => [null, 'fallback-token', 'fallback-token', 'GH_TOKEN'],
        'placeholder ignored' => ['ghp_your_token_here', 'fallback-token', 'fallback-token', 'GH_TOKEN'],
        'CLI fallback' => ['', '', 'cli-token', 'GitHub CLI'],
    ]);

    it('provides authentication status information', function () {
        $status = TokenResolver::getAuthenticationStatus();

        expect($status)->toBeString();

        // Status should contain either "Authenticated" or "No authentication"
        $hasAuth = str_contains($status, 'Authenticated') || str_contains($status, 'No authentication');
        expect($hasAuth)->toBeTrue();
    });

    it('provides helpful authentication guidance', function () {
        $help = TokenResolver::getAuthenticationHelp();

        expect($help)->toBeString()
            ->and($help)->toContain('GitHub CLI')
            ->and($help)->toContain('Environment variable')
            ->and($help)->toContain('rate limits');
    });

    it('handles requests without authentication gracefully', function () {
        // Mock a successful response for public repo with all required fields
        $mockClient = new MockClient([
            MockResponse::make([
                'id' => 123,
                'node_id' => 'MDEwOlJlcG9zaXRvcnkxMjM=',
                'name' => 'public-repo',
                'full_name' => 'owner/public-repo',
                'private' => false,
                'owner' => [
                    'login' => 'owner',
                    'id' => 1,
                    'node_id' => 'MDQ6VXNlcjE=',
                    'avatar_url' => 'https://avatars.githubusercontent.com/u/1?v=4',
                    'gravatar_id' => '',
                    'url' => 'https://api.github.com/users/owner',
                    'html_url' => 'https://github.com/owner',
                    'followers_url' => 'https://api.github.com/users/owner/followers',
                    'following_url' => 'https://api.github.com/users/owner/following{/other_user}',
                    'gists_url' => 'https://api.github.com/users/owner/gists{/gist_id}',
                    'starred_url' => 'https://api.github.com/users/owner/starred{/owner}{/repo}',
                    'subscriptions_url' => 'https://api.github.com/users/owner/subscriptions',
                    'organizations_url' => 'https://api.github.com/users/owner/orgs',
                    'repos_url' => 'https://api.github.com/users/owner/repos',
                    'events_url' => 'https://api.github.com/users/owner/events{/privacy}',
                    'received_events_url' => 'https://api.github.com/users/owner/received_events',
                    'type' => 'User',
                    'site_admin' => false,
                ],
                'html_url' => 'https://github.com/owner/public-repo',
                'description' => 'A public repository',
                'fork' => false,
                'url' => 'https://api.github.com/repos/owner/public-repo',
                'forks_url' => 'https://api.github.com/repos/owner/public-repo/forks',
                'keys_url' => 'https://api.github.com/repos/owner/public-repo/keys{/key_id}',
                'collaborators_url' => 'https://api.github.com/repos/owner/public-repo/collaborators{/collaborator}',
                'teams_url' => 'https://api.github.com/repos/owner/public-repo/teams',
                'hooks_url' => 'https://api.github.com/repos/owner/public-repo/hooks',
                'issue_events_url' => 'https://api.github.com/repos/owner/public-repo/issues/events{/number}',
                'events_url' => 'https://api.github.com/repos/owner/public-repo/events',
                'assignees_url' => 'https://api.github.com/repos/owner/public-repo/assignees{/user}',
                'branches_url' => 'https://api.github.com/repos/owner/public-repo/branches{/branch}',
                'tags_url' => 'https://api.github.com/repos/owner/public-repo/tags',
                'blobs_url' => 'https://api.github.com/repos/owner/public-repo/git/blobs{/sha}',
                'git_tags_url' => 'https://api.github.com/repos/owner/public-repo/git/tags{/sha}',
                'git_refs_url' => 'https://api.github.com/repos/owner/public-repo/git/refs{/sha}',
                'trees_url' => 'https://api.github.com/repos/owner/public-repo/git/trees{/sha}',
                'statuses_url' => 'https://api.github.com/repos/owner/public-repo/statuses/{sha}',
                'languages_url' => 'https://api.github.com/repos/owner/public-repo/languages',
                'stargazers_url' => 'https://api.github.com/repos/owner/public-repo/stargazers',
                'contributors_url' => 'https://api.github.com/repos/owner/public-repo/contributors',
                'subscribers_url' => 'https://api.github.com/repos/owner/public-repo/subscribers',
                'subscription_url' => 'https://api.github.com/repos/owner/public-repo/subscription',
                'commits_url' => 'https://api.github.com/repos/owner/public-repo/commits{/sha}',
                'git_commits_url' => 'https://api.github.com/repos/owner/public-repo/git/commits{/sha}',
                'comments_url' => 'https://api.github.com/repos/owner/public-repo/comments{/number}',
                'issue_comment_url' => 'https://api.github.com/repos/owner/public-repo/issues/comments{/number}',
                'contents_url' => 'https://api.github.com/repos/owner/public-repo/contents/{+path}',
                'compare_url' => 'https://api.github.com/repos/owner/public-repo/compare/{base}...{head}',
                'merges_url' => 'https://api.github.com/repos/owner/public-repo/merges',
                'archive_url' => 'https://api.github.com/repos/owner/public-repo/{archive_format}{/ref}',
                'downloads_url' => 'https://api.github.com/repos/owner/public-repo/downloads',
                'issues_url' => 'https://api.github.com/repos/owner/public-repo/issues{/number}',
                'pulls_url' => 'https://api.github.com/repos/owner/public-repo/pulls{/number}',
                'milestones_url' => 'https://api.github.com/repos/owner/public-repo/milestones{/number}',
                'notifications_url' => 'https://api.github.com/repos/owner/public-repo/notifications{?since,all,participating}',
                'labels_url' => 'https://api.github.com/repos/owner/public-repo/labels{/name}',
                'releases_url' => 'https://api.github.com/repos/owner/public-repo/releases{/id}',
                'deployments_url' => 'https://api.github.com/repos/owner/public-repo/deployments',
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'pushed_at' => '2024-01-01T00:00:00Z',
                'git_url' => 'git://github.com/owner/public-repo.git',
                'ssh_url' => 'git@github.com:owner/public-repo.git',
                'clone_url' => 'https://github.com/owner/public-repo.git',
                'svn_url' => 'https://github.com/owner/public-repo',
                'homepage' => null,
                'size' => 100,
                'stargazers_count' => 0,
                'watchers_count' => 0,
                'language' => 'PHP',
                'has_issues' => true,
                'has_projects' => true,
                'has_downloads' => true,
                'has_wiki' => true,
                'has_pages' => false,
                'has_discussions' => false,
                'forks_count' => 0,
                'mirror_url' => null,
                'archived' => false,
                'disabled' => false,
                'open_issues_count' => 0,
                'license' => null,
                'allow_forking' => true,
                'is_template' => false,
                'web_commit_signoff_required' => false,
                'topics' => [],
                'visibility' => 'public',
                'forks' => 0,
                'open_issues' => 0,
                'watchers' => 0,
                'default_branch' => 'main',
                'permissions' => [
                    'admin' => false,
                    'maintain' => false,
                    'push' => false,
                    'triage' => false,
                    'pull' => true,
                ],
            ], 200),
        ]);

        // Create connector without token
        $connector = new GithubConnector('');
        $connector->withMockClient($mockClient);

        // Create Github instance with unauthenticated connector
        $github = new JordanPartridge\GithubClient\Github($connector);

        // Should be able to get public repo without auth
        $repo = $github->getRepo('owner/public-repo');

        expect($repo)->toBeInstanceOf(RepoData::class)
            ->and($repo->name)->toBe('public-repo')
            ->and($repo->private)->toBeFalse();
    });

    it('clears the authentication source when credentials are no longer available', function () {
        config(['github-client.token' => 'initial-token']);
        expect(TokenResolver::resolve())->toBe('initial-token');

        config(['github-client.token' => null, 'github-client.gh_token' => null]);
        Process::fake(['*' => Process::result(exitCode: 1)]);

        expect(TokenResolver::resolve())->toBeNull()
            ->and(TokenResolver::getLastSource())->toBeNull()
            ->and(TokenResolver::getAuthenticationStatus())->toBe('No authentication (public access only)');
    });
});
