<?php

namespace JordanPartridge\GithubClient;

use ConduitUi\GitHubConnector\GithubConnector;
use JordanPartridge\GithubClient\Auth\GithubOAuth;
use JordanPartridge\GithubClient\Auth\TokenResolver;
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

        // Register TokenResolver as singleton
        $this->app->singleton(TokenResolver::class, function () {
            return new TokenResolver();
        });

        $this->app->singleton(GithubConnector::class, function ($app) {
            $tokenResolver = $app->make(TokenResolver::class);

            // Try to resolve a token (not required - allows public access)
            $token = $tokenResolver->resolve(required: false);

            return new GithubConnector($token);
        });

        $this->app->bind(Github::class, function ($app) {
            return new Github($app->make(GithubConnector::class));
        });

        $this->app->singleton(GithubOAuth::class, function () {
            return new GithubOAuth(
                config('github-client.oauth.client_id'),
                config('github-client.oauth.client_secret'),
                config('github-client.oauth.redirect_url'),
            );
        });
    }
}
