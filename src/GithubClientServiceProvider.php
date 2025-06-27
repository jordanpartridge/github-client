<?php

namespace JordanPartridge\GithubClient;

use ConduitUi\GitHubConnector\GithubConnector;
use JordanPartridge\GithubClient\Auth\GithubOAuth;
use JordanPartridge\GithubClient\Commands\GithubClientCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GithubClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('github-client')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_github_client_table')
            ->hasCommand(GithubClientCommand::class);

        $this->app->singleton(GithubConnector::class, function () {
            $token = config('github-client.token');

            if (empty($token)) {
                throw new \InvalidArgumentException('GitHub token is required. Please set GITHUB_TOKEN environment variable.');
            }

            return new GithubConnector($token);
        });

        $this->app->bind(Github::class, function ($app) {
            return new Github($app->make(GithubConnector::class));
        });

        $this->app->singleton(GithubOAuth::class, function () {
            return new GithubOAuth(
                config('github-client.oauth.client_id'),
                config('github-client.oauth.client_secret'),
                config('github-client.oauth.redirect_url')
            );
        });
    }
}
