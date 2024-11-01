<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Commands\GithubClientCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GithubClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('github-client')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_github_client_table')
            ->hasCommand(GithubClientCommand::class);

        $this->app->singleton(GithubConnector::class, function () {
            return new GithubConnector(config('github-client.token'));
        });
    }
}
