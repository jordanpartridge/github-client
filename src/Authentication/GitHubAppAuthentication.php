<?php

namespace JordanPartridge\GithubClient\Authentication;

use Firebase\JWT\JWT;
use JordanPartridge\GithubClient\Support\AuthenticationStrategy;

class GitHubAppAuthentication implements AuthenticationStrategy
{
    private string $appId;

    private string $installationId;

    private string $privateKey;

    private ?string $installationToken = null;

    private int $tokenExpiration = 0;

    public function __construct(string $appId, string $installationId, string $privateKey)
    {
        $this->appId = $appId;
        $this->installationId = $installationId;
        $this->privateKey = $privateKey;
    }

    public function getAuthorizationHeader(): string
    {
        $this->validateAndRefreshToken();

        return "token {$this->installationToken}";
    }

    public function validateAndRefreshToken(): void
    {
        if ($this->shouldRefreshToken()) {
            $this->generateInstallationToken();
        }
    }

    private function shouldRefreshToken(): bool
    {
        return $this->installationToken === null || time() >= $this->tokenExpiration;
    }

    private function generateInstallationToken(): void
    {
        $jwt = $this->generateJwt();

        // Simulate GitHub API call to get installation token
        // In a real implementation, this would be an actual API request
        $this->installationToken = $this->fetchInstallationToken($jwt);
        $this->tokenExpiration = time() + 3600; // Token valid for 1 hour
    }

    private function generateJwt(): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 600,
            'iss' => $this->appId,
        ];

        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    private function fetchInstallationToken(string $jwt): string
    {
        // Simulated token fetch - replace with actual GitHub API call
        return 'github_app_installation_token_'.uniqid();
    }
}
