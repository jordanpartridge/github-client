<?php

namespace JordanPartridge\GithubClient\Support;

use Illuminate\Support\ServiceProvider;

abstract class AbstractGithubClientServiceProvider extends ServiceProvider
{
    /**
     * Determine if the application is Laravel Zero.
     *
     * @return bool
     */
    protected function isLaravelZero(): bool
    {
        return class_exists('\Laravel\Zero\Framework\Application');
    }

    /**
     * Determine if the application is standard Laravel.
     *
     * @return bool
     */
    protected function isLaravel(): bool
    {
        return !$this->isLaravelZero() && class_exists('\Illuminate\Foundation\Application');
    }

    /**
     * Register configuration based on the framework.
     */
    protected function registerConfiguration(): void
    {
        if ($this->isLaravel()) {
            $this->publishes([
                __DIR__.'/../../config/github-client.php' => config_path('github-client.php'),
            ]);
        } elseif ($this->isLaravelZero()) {
            // Laravel Zero specific configuration
            $this->app->configPath('github-client.php');
        }

        $this->mergeConfigFrom(
            __DIR__.'/../../config/github-client.php',
            'github-client'
        );
    }

    /**
     * Register migrations based on the framework.
     */
    protected function registerMigrations(): void
    {
        if ($this->isLaravel()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
        // Laravel Zero typically doesn't use database migrations
    }
}