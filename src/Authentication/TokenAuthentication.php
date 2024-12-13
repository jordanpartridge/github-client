<?php

namespace JordanPartridge\GithubClient\Authentication;

use JordanPartridge\GithubClient\Support\AuthenticationStrategy;

class TokenAuthentication implements AuthenticationStrategy
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function getAuthorizationHeader(): string
    {
        return "token {$this->token}";
    }

    public function validateAndRefreshToken(): void
    {
        // Simple token validation
        if (empty($this->token)) {
            throw new \InvalidArgumentException('Authentication token is empty');
        }
    }
}
