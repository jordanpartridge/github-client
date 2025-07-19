<?php

namespace JordanPartridge\GithubClient\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use JordanPartridge\GithubClient\GithubClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'JordanPartridge\\GithubClient\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            GithubClientServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Set up github-client configuration for testing
        config()->set('github-client.token', 'test-token');
        config()->set('github-client.base_url', 'https://api.github.com');

        /*
        $migration = include __DIR__.'/../database/migrations/create_github-client_table.php.stub';
        $migration->up();
        */
    }

    protected function createMockUserData(string $login = 'testuser', int $id = 1): array
    {
        return [
            'login' => $login,
            'id' => $id,
            'node_id' => 'MDQ6VXNlcjE=',
            'avatar_url' => "https://github.com/{$login}.png",
            'gravatar_id' => '',
            'url' => "https://api.github.com/users/{$login}",
            'html_url' => "https://github.com/{$login}",
            'followers_url' => "https://api.github.com/users/{$login}/followers",
            'following_url' => "https://api.github.com/users/{$login}/following{/other_user}",
            'gists_url' => "https://api.github.com/users/{$login}/gists{/gist_id}",
            'starred_url' => "https://api.github.com/users/{$login}/starred{/owner}{/repo}",
            'subscriptions_url' => "https://api.github.com/users/{$login}/subscriptions",
            'organizations_url' => "https://api.github.com/users/{$login}/orgs",
            'repos_url' => "https://api.github.com/users/{$login}/repos",
            'events_url' => "https://api.github.com/users/{$login}/events{/privacy}",
            'received_events_url' => "https://api.github.com/users/{$login}/received_events",
            'type' => 'User',
            'user_view_type' => 'public',
            'site_admin' => false,
        ];
    }

    protected function createMockRepoData(string $name = 'test-repo', int $id = 1, string $owner = 'testuser'): array
    {
        return [
            'id' => $id,
            'node_id' => 'MDEwOlJlcG9zaXRvcnkxMjk2MjY5',
            'name' => $name,
            'full_name' => "{$owner}/{$name}",
            'private' => false,
            'owner' => $this->createMockUserData($owner, $id),
            'html_url' => "https://github.com/{$owner}/{$name}",
            'description' => 'This is a test repository',
            'fork' => false,
            'url' => "https://api.github.com/repos/{$owner}/{$name}",
            'forks_url' => "https://api.github.com/repos/{$owner}/{$name}/forks",
            'keys_url' => "https://api.github.com/repos/{$owner}/{$name}/keys{/key_id}",
            'collaborators_url' => "https://api.github.com/repos/{$owner}/{$name}/collaborators{/collaborator}",
            'teams_url' => "https://api.github.com/repos/{$owner}/{$name}/teams",
            'hooks_url' => "https://api.github.com/repos/{$owner}/{$name}/hooks",
            'issue_events_url' => "https://api.github.com/repos/{$owner}/{$name}/issues/events{/number}",
            'events_url' => "https://api.github.com/repos/{$owner}/{$name}/events",
            'assignees_url' => "https://api.github.com/repos/{$owner}/{$name}/assignees{/user}",
            'branches_url' => "https://api.github.com/repos/{$owner}/{$name}/branches{/branch}",
            'tags_url' => "https://api.github.com/repos/{$owner}/{$name}/tags",
            'blobs_url' => "https://api.github.com/repos/{$owner}/{$name}/git/blobs{/sha}",
            'git_tags_url' => "https://api.github.com/repos/{$owner}/{$name}/git/tags{/sha}",
            'git_refs_url' => "https://api.github.com/repos/{$owner}/{$name}/git/refs{/sha}",
            'trees_url' => "https://api.github.com/repos/{$owner}/{$name}/git/trees{/sha}",
            'statuses_url' => "https://api.github.com/repos/{$owner}/{$name}/statuses/{sha}",
            'languages_url' => "https://api.github.com/repos/{$owner}/{$name}/languages",
            'stargazers_url' => "https://api.github.com/repos/{$owner}/{$name}/stargazers",
            'contributors_url' => "https://api.github.com/repos/{$owner}/{$name}/contributors",
            'subscribers_url' => "https://api.github.com/repos/{$owner}/{$name}/subscribers",
            'subscription_url' => "https://api.github.com/repos/{$owner}/{$name}/subscription",
            'commits_url' => "https://api.github.com/repos/{$owner}/{$name}/commits{/sha}",
            'git_commits_url' => "https://api.github.com/repos/{$owner}/{$name}/git/commits{/sha}",
            'comments_url' => "https://api.github.com/repos/{$owner}/{$name}/comments{/number}",
            'issue_comment_url' => "https://api.github.com/repos/{$owner}/{$name}/issues/comments{/number}",
            'contents_url' => "https://api.github.com/repos/{$owner}/{$name}/contents/{+path}",
            'compare_url' => "https://api.github.com/repos/{$owner}/{$name}/compare/{base}...{head}",
            'merges_url' => "https://api.github.com/repos/{$owner}/{$name}/merges",
            'archive_url' => "https://api.github.com/repos/{$owner}/{$name}/{archive_format}{/ref}",
            'downloads_url' => "https://api.github.com/repos/{$owner}/{$name}/downloads",
            'issues_url' => "https://api.github.com/repos/{$owner}/{$name}/issues{/number}",
            'pulls_url' => "https://api.github.com/repos/{$owner}/{$name}/pulls{/number}",
            'milestones_url' => "https://api.github.com/repos/{$owner}/{$name}/milestones{/number}",
            'notifications_url' => "https://api.github.com/repos/{$owner}/{$name}/notifications{?since,all,participating}",
            'labels_url' => "https://api.github.com/repos/{$owner}/{$name}/labels{/name}",
            'releases_url' => "https://api.github.com/repos/{$owner}/{$name}/releases{/id}",
            'deployments_url' => "https://api.github.com/repos/{$owner}/{$name}/deployments",
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'pushed_at' => '2024-01-01T00:00:00Z',
            'git_url' => "git://github.com/{$owner}/{$name}.git",
            'ssh_url' => "git@github.com:{$owner}/{$name}.git",
            'clone_url' => "https://github.com/{$owner}/{$name}.git",
            'svn_url' => "https://github.com/{$owner}/{$name}",
            'homepage' => null,
            'size' => 108,
            'stargazers_count' => 80,
            'watchers_count' => 9,
            'language' => 'PHP',
            'has_issues' => true,
            'has_projects' => true,
            'has_wiki' => true,
            'has_pages' => false,
            'has_downloads' => true,
            'has_discussions' => false,
            'forks_count' => 9,
            'mirror_url' => null,
            'archived' => false,
            'disabled' => false,
            'open_issues_count' => 0,
            'license' => [
                'key' => 'mit',
                'name' => 'MIT License',
                'spdx_id' => 'MIT',
                'url' => 'https://api.github.com/licenses/mit',
                'node_id' => 'MDc6TGljZW5zZW1pdA==',
            ],
            'allow_forking' => true,
            'is_template' => false,
            'web_commit_signoff_required' => false,
            'topics' => [],
            'visibility' => 'public',
            'forks' => 9,
            'open_issues' => 0,
            'watchers' => 80,
            'default_branch' => 'main',
        ];
    }
}
