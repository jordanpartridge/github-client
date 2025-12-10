<?php

namespace JordanPartridge\GithubClient\Auth;

use Illuminate\Support\Facades\Process;

/**
 * Resolves GitHub authentication tokens from multiple sources.
 *
 * Priority order:
 * 1. GitHub CLI (gh auth token)
 * 2. Environment variables (GITHUB_TOKEN, GH_TOKEN)
 * 3. Laravel config
 * 4. Return null for optional auth (public repos)
 */
class TokenResolver
{
    protected static ?string $lastSource = null;

    /**
     * Resolve token from all available sources.
     */
    public static function resolve(): ?string
    {
        // 1. Check environment variables first (fast, no external process)
        if ($token = self::getEnvironmentToken()) {
            self::$lastSource = env('GITHUB_TOKEN') ? 'GITHUB_TOKEN' : 'GH_TOKEN';

            return $token;
        }

        // 2. Check Laravel config
        if ($token = self::getConfigToken()) {
            self::$lastSource = 'config';

            return $token;
        }

        // 3. Check GitHub CLI last (slower, spawns external process)
        if ($token = self::getGitHubCliToken()) {
            self::$lastSource = 'GitHub CLI';

            return $token;
        }

        // 4. Return null - authentication is optional for public repos
        self::$lastSource = null;

        return null;
    }

    /**
     * Get token from GitHub CLI if available.
     */
    private static function getGitHubCliToken(): ?string
    {
        try {
            // Check if gh CLI is available (with timeout to prevent hanging in CI)
            $result = Process::timeout(2)->run('which gh');
            if (! $result->successful()) {
                return null;
            }

            // Get token from gh CLI (with timeout to prevent hanging if not authenticated)
            $result = Process::timeout(3)->run('gh auth token');
            if ($result->successful()) {
                $token = trim($result->output());
                if (! empty($token)) {
                    return $token;
                }
            }
        } catch (\Exception) {
            // gh CLI not available, not authenticated, or timed out
        }

        return null;
    }

    /**
     * Get token from environment variables.
     */
    private static function getEnvironmentToken(): ?string
    {
        // Check GITHUB_TOKEN first (standard)
        $token = env('GITHUB_TOKEN');
        if (! empty($token)) {
            return $token;
        }

        // Check GH_TOKEN (GitHub CLI convention)
        $token = env('GH_TOKEN');
        if (! empty($token)) {
            return $token;
        }

        return null;
    }

    /**
     * Get token from Laravel config.
     */
    private static function getConfigToken(): ?string
    {
        $token = config('github-client.token');

        // Only return if not empty and not a placeholder
        if (! empty($token) && $token !== 'your-github-token-here') {
            return $token;
        }

        return null;
    }

    /**
     * Check if any authentication is available.
     */
    public static function hasAuthentication(): bool
    {
        return self::resolve() !== null;
    }

    /**
     * Get a descriptive message about authentication status.
     */
    public static function getAuthenticationStatus(): string
    {
        if ($token = self::getGitHubCliToken()) {
            return 'Authenticated via GitHub CLI';
        }

        if ($token = self::getEnvironmentToken()) {
            $source = env('GITHUB_TOKEN') ? 'GITHUB_TOKEN' : 'GH_TOKEN';

            return "Authenticated via environment variable ({$source})";
        }

        if ($token = self::getConfigToken()) {
            return 'Authenticated via config file';
        }

        return 'No authentication (public access only)';
    }

    /**
     * Get helpful error message for authentication issues.
     */
    public static function getAuthenticationHelp(): string
    {
        return <<<'HELP'
        GitHub authentication is recommended for better rate limits.
        
        You can authenticate using one of these methods:
        
        1. GitHub CLI (recommended):
           gh auth login
        
        2. Environment variable:
           export GITHUB_TOKEN=your_token_here
        
        3. Laravel config:
           Set token in config/github-client.php
        
        Note: Public repositories can be accessed without authentication,
        but rate limits are much lower (60 requests/hour vs 5000 with auth).
        HELP;
    }

    /**
     * Get the last resolved authentication source.
     */
    public static function getLastSource(): ?string
    {
        return self::$lastSource;
    }
}
