<?php

namespace JordanPartridge\GithubClient\Auth;

use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

/**
 * Personal Access Token authentication strategy.
 */
class TokenAuthentication implements AuthenticationStrategy
{
    public function __construct(
        private readonly string $token
    ) {}

    public function getAuthorizationHeader(): string
    {
        return "Bearer {$this->token}";
    }

    public function validate(): void
    {
        if (empty($this->token)) {
            throw AuthenticationException::missingToken();
        }

        if (strlen($this->token) < 10) {
            throw AuthenticationException::invalidToken('Token appears to be too short');
        }

        // GitHub tokens typically start with specific prefixes
        if (! $this->hasValidTokenPrefix()) {
            throw AuthenticationException::invalidToken('Token format appears invalid');
        }
    }

    public function getType(): string
    {
        return 'token';
    }

    public function needsRefresh(): bool
    {
        // Personal access tokens don't auto-refresh
        return false;
    }

    public function refresh(): void
    {
        // Personal access tokens don't auto-refresh
    }

    private function hasValidTokenPrefix(): bool
    {
        $validPrefixes = [
            'ghp_',  // Personal access token
            'gho_',  // OAuth token
            'ghu_',  // User-to-server token
            'ghs_',  // Server-to-server token
            'ghr_',  // Refresh token
        ];

        foreach ($validPrefixes as $prefix) {
            if (str_starts_with($this->token, $prefix)) {
                return true;
            }
        }

        // Also allow legacy tokens without prefixes (40 characters, alphanumeric)
        return preg_match('/^[a-f0-9]{40}$/', $this->token) === 1;
    }
}
