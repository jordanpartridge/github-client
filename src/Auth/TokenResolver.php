<?php

namespace JordanPartridge\GithubClient\Auth;

use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Resolves GitHub authentication tokens from multiple sources in priority order:
 * 1. GitHub CLI token (gh auth token)
 * 2. Environment variables (GITHUB_TOKEN, GH_TOKEN)
 * 3. Config file
 * 4. Returns null if no token found (for public access)
 */
class TokenResolver
{
    private const GITHUB_TOKEN_ENV_VARS = ['GITHUB_TOKEN', 'GH_TOKEN'];

    /**
     * Resolve a GitHub token from available sources.
     *
     * @param  bool  $required  Whether authentication is required for the operation
     *
     * @return string|null The resolved token, or null if not required and not found
     *
     * @throws AuthenticationException When authentication is required but no valid token found
     */
    public function resolve(bool $required = false): ?string
    {
        // Priority 1: GitHub CLI token
        if ($token = $this->getGitHubCliToken()) {
            return $token;
        }

        // Priority 2: Environment variables
        if ($token = $this->getEnvironmentToken()) {
            return $token;
        }

        // Priority 3: Config file
        if ($token = $this->getConfigToken()) {
            return $token;
        }

        // If authentication is required but no token found, throw exception
        if ($required) {
            throw AuthenticationException::noTokenFound($this->getAuthenticationGuidance());
        }

        // Return null for optional authentication (public access)
        return null;
    }

    /**
     * Check if GitHub CLI is authenticated.
     */
    public function hasGitHubCliAuth(): bool
    {
        return $this->getGitHubCliToken() !== null;
    }

    /**
     * Get authentication guidance for users.
     */
    public function getAuthenticationGuidance(): string
    {
        $guidance = "No GitHub authentication found. To authenticate, use one of these methods:\n\n";

        if ($this->isGitHubCliAvailable()) {
            $guidance .= "1. GitHub CLI (recommended): Run 'gh auth login'\n";
        } else {
            $guidance .= "1. Install GitHub CLI and run 'gh auth login' (recommended)\n";
        }

        $guidance .= "2. Set GITHUB_TOKEN environment variable\n";
        $guidance .= "3. Set GH_TOKEN environment variable\n";
        $guidance .= "4. Configure token in config/github-client.php\n\n";
        $guidance .= 'For public repositories, authentication is optional but increases rate limits.';

        return $guidance;
    }

    /**
     * Get token from GitHub CLI if available and authenticated.
     */
    protected function getGitHubCliToken(): ?string
    {
        if (! $this->isGitHubCliAvailable()) {
            return null;
        }

        try {
            $process = new Process(['gh', 'auth', 'token']);
            $process->run();

            if ($process->isSuccessful()) {
                $token = trim($process->getOutput());

                return ! empty($token) ? $token : null;
            }
        } catch (ProcessFailedException $e) {
            // GitHub CLI not authenticated or other error
            return null;
        } catch (\Exception $e) {
            // Any other error (process not found, etc.)
            return null;
        }

        return null;
    }

    /**
     * Get token from environment variables.
     */
    protected function getEnvironmentToken(): ?string
    {
        foreach (self::GITHUB_TOKEN_ENV_VARS as $envVar) {
            $token = getenv($envVar) ?: null;
            if (! empty($token)) {
                return $token;
            }
        }

        return null;
    }

    /**
     * Get token from config file.
     */
    protected function getConfigToken(): ?string
    {
        if (! function_exists('config')) {
            return null;
        }

        $token = config('github-client.token');

        return ! empty($token) ? $token : null;
    }

    /**
     * Check if GitHub CLI is available on the system.
     */
    protected function isGitHubCliAvailable(): bool
    {
        try {
            $process = new Process(['gh', '--version']);
            $process->run();

            return $process->isSuccessful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
