<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Auth\GithubOAuth;
use JordanPartridge\GithubClient\Commands\GithubClientCommand;
use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
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

        $this->app->singleton(GithubConnectorInterface::class, function () {
            return new GithubConnector(config('github-client.token'));
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
