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
            \Spatie\LaravelData\LaravelDataServiceProvider::class,
            GithubClientServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Set up spatie/laravel-data configuration
        config()->set('data.validation_strategy', 'disabled');

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
}
