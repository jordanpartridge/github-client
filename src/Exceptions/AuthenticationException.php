<?php

namespace JordanPartridge\GithubClient\Exceptions;

/**
 * Exception thrown when authentication fails or token is invalid.
 */
class AuthenticationException extends GithubClientException
{
    protected string $authenticationType;

    public function __construct(
        string $message,
        string $authenticationType = 'token',
        int $code = 401,
        ?\Throwable $previous = null,
    ) {
        $this->authenticationType = $authenticationType;

        parent::__construct($message, $code, $previous, [
            'authentication_type' => $authenticationType,
        ]);
    }

    public function getAuthenticationType(): string
    {
        return $this->authenticationType;
    }

    public static function invalidToken(string $message = 'Invalid or expired GitHub token'): self
    {
        return new self($message, 'token');
    }

    public static function missingToken(string $message = 'GitHub token is required but not provided'): self
    {
        return new self($message, 'token', 400);
    }

    public static function githubAppAuthFailed(string $message = 'GitHub App authentication failed'): self
    {
        return new self($message, 'github_app');
    }

    public static function noTokenFound(string $guidance = 'No GitHub token found'): self
    {
        return new self("Authentication required: {$guidance}", 'token', 400);
    }
}
