<?php

namespace JordanPartridge\GithubClient;

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

        $this->app->singleton(Github::class, function () {
            return new Github(config('github-client.token'));
        });
    }
}
