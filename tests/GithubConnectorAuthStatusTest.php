<?php

use JordanPartridge\GithubClient\Auth\AuthenticationStrategy;
use JordanPartridge\GithubClient\Connectors\GithubConnector;
use JordanPartridge\GithubClient\Exceptions\ApiException;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use JordanPartridge\GithubClient\Exceptions\RateLimitException;
use JordanPartridge\GithubClient\Requests\User;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function githubAppConnector(): GithubConnector
{
    $strategy = new class () implements AuthenticationStrategy {
        public function getAuthorizationHeader(): string
        {
            return 'Bearer installation-token';
        }

        public function validate(): void {}

        public function getType(): string
        {
            return 'github_app';
        }

        public function needsRefresh(): bool
        {
            return false;
        }

        public function refresh(): void {}
    };

    return new GithubConnector($strategy);
}

it('reports a strategy-based connector as authenticated', function () {
    $status = githubAppConnector()->getAuthenticationStatus();

    expect($status['authenticated'])->toBeTrue()
        ->and($status['source'])->toBe('github_app')
        ->and($status['token_preview'])->toBeNull();
});

it('does not describe a strategy-based connector as unauthenticated when a request is rejected', function (int $status, string $message, string $exception, string $expected) {
    $connector = githubAppConnector();
    $connector->withMockClient(new MockClient([
        MockResponse::make(['message' => $message], $status),
    ]));

    $error = $connector->getRequestException($connector->send(new User()));

    expect($error)->toBeInstanceOf($exception)
        ->and($error->getMessage())->toContain($expected)
        ->and($error->getMessage())->not->toContain('unauthenticated')
        ->and($error->getMessage())->not->toContain('requires authentication')
        ->and($error->getMessage())->not->toContain('may require authentication');
})->with([
    '401' => [401, 'Bad credentials', AuthenticationException::class, 'Current auth source: github_app'],
    '403' => [403, 'Resource not accessible by integration', ApiException::class, 'required scopes'],
    '429' => [429, 'API rate limit exceeded', RateLimitException::class, 'Authenticated via: github_app'],
]);
