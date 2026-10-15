<?php

namespace JordanPartridge\GithubClient\Auth;

use Illuminate\Support\Facades\Process;

/**
 * Resolves GitHub authentication tokens from multiple sources.
 *
 * Priority order:
 * 1. config('github-client.token'), normally GITHUB_TOKEN
 * 2. config('github-client.gh_token'), normally GH_TOKEN
 * 3. GitHub CLI (gh auth token)
 * 4. null when nothing is configured (public repositories)
 */
class TokenResolver
{
    protected static ?string $lastSource = null;

    /**
     * Resolve token from all available sources.
     */
    public static function resolve(): ?string
    {
        if ($token = self::configuredToken('github-client.token')) {
            self::$lastSource = 'config';

            return $token;
        }

        if ($token = self::configuredToken('github-client.gh_token')) {
            self::$lastSource = 'GH_TOKEN';

            return $token;
        }

        if ($token = self::getGitHubCliToken()) {
            self::$lastSource = 'GitHub CLI';

            return $token;
        }

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
     * Read a token from config. Empty strings and the README placeholder are ignored.
     */
    private static function configuredToken(string $key): ?string
    {
        $token = config($key);

        if (! is_string($token) || $token === '' || $token === 'your-github-token-here' || $token === 'ghp_your_token_here') {
            return null;
        }

        return $token;
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
        if (self::configuredToken('github-client.token') || self::configuredToken('github-client.gh_token')) {
            return 'Authenticated via config file';
        }

        if (self::getGitHubCliToken()) {
            return 'Authenticated via GitHub CLI';
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

        1. Environment variable:
           GITHUB_TOKEN or GH_TOKEN in .env
           (read through config/github-client.php)

        2. Laravel config:
           Set token in config/github-client.php

        3. GitHub CLI:
           gh auth login

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
