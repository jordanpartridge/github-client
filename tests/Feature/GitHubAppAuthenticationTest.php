<?php

use JordanPartridge\GithubClient\Auth\GitHubAppAuthentication;
use JordanPartridge\GithubClient\Connectors\GithubConnector;
use JordanPartridge\GithubClient\Data\Installations\InstallationData;
use JordanPartridge\GithubClient\Data\Installations\InstallationTokenData;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use JordanPartridge\GithubClient\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('GitHub App Authentication', function () {
    beforeEach(function () {
        // Mock private key for testing
        $this->privateKey = <<<'KEY'
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA0Z3VS5JJcds3xfn/ygWyF8l0qBnX7l+4MNwMPkN6YODeFbRu
Z0p3AevFKhqiLVT6M3p6wTtQlKJPxjGPMd1YqV0wPG6NNJVFfLNpjt8pSfSiQ7cr
Gw2bqklqjR7lhY0lBF/2YY7v8wWPqfR7FLK7yLNVYC7H6FqN0qMBnJbEbHQk7i0+
uNcm2Ql0cX2XpdjHqKfJ2QmKxKCb8v3HDNNnUU2P+rKxL0FwRLBXRfNRnBBMvVqf
Ll1gxYEGmVGQKWp0pVjJwPTbh8L8fRrSYfLvhNYVPgjNJ9j8UhOCiGfNJDDU4PQp
0SeFZnWPDwGqWVYcNGX5pYHGGqC5pxVJcFNkqQIDAQABAoIBACLQGLXxKiP5N7u+
CY1L5KGmGQCyBpF3YJZfMx+eWRMNT5dTLhE4LPZM5GlKEQ3rqN3nJN7wKQPGsWqG
T7KOKGmMvXNSx8m2YRqT7WBEKb6nW1YR8nO8CxDXvYqBMgHCNJuT8K8IHHnPcKqN
+dQf0bxXq0YR7hBDqP6sV8gGPqHfBLv5wNl4P7+gGwWlJHkqLv5fJLnYFHKNBrMJ
2P7vqNjE7UQ8JH5qL8fKGpL7DqPNRJK2vP5HqBkJNQGPqNVL7M+Q8PxNgQP9LqYN
FHQqL7NJqPvKxHNL8Q6P7NqLvQH8PxQGLvN7JqPNFH5Q8LvP7NqLHQqP7NqLvQH8
PxQGLvN7JqPNFHECgYEA7ZR3vN8m2xP5QqLvN7JqPNFHQqL7NqLvQH8PxQGLvN7J
qPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQ
H8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPN
FHQqL7NqLvQH8CkCgYEA4YP5qLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7
NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN
7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqL
vQH8PxQGLvN7JqPNFHQCgYEA2P5qLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQ
qL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQG
LvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7
NqLvQH8PxQGLvN7JqPNFHQkCgYBqLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQ
qL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQG
LvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7
NqLvQH8PxQGLvN7JqPNFHQQKBgQC5qLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNF
HQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8Px
QGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQqL7NqLvQH8PxQGLvN7JqPNFHQq
L7NqLvQH8PxQGLvN7JqPNFHQ==
-----END RSA PRIVATE KEY-----
KEY;
    });

    it('validates GitHub App credentials', function () {
        $auth = new GitHubAppAuthentication(
            appId: '12345',
            privateKey: $this->privateKey,
        );

        expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
    });

    it('throws exception for invalid app ID', function () {
        $auth = new GitHubAppAuthentication(
            appId: 'invalid',
            privateKey: $this->privateKey,
        );

        expect(fn () => $auth->validate())->toThrow(
            AuthenticationException::class,
            'App ID must be numeric',
        );
    });

    it('throws exception for empty private key', function () {
        expect(fn () => new GitHubAppAuthentication(
            appId: '12345',
            privateKey: '',
        ))->toThrow(AuthenticationException::class);
    });

    it('generates JWT token for app-level auth', function () {
        $auth = new GitHubAppAuthentication(
            appId: '12345',
            privateKey: $this->privateKey,
        );

        $header = $auth->getAuthorizationHeader();

        expect($header)->toStartWith('Bearer ')
            ->and(strlen($header))->toBeGreaterThan(100);
    });

    it('returns installation token when set', function () {
        $auth = new GitHubAppAuthentication(
            appId: '12345',
            privateKey: $this->privateKey,
            installationId: '67890',
        );

        $expiry = new DateTimeImmutable('+1 hour');
        $auth->setInstallationToken('test_installation_token', $expiry);

        $header = $auth->getAuthorizationHeader();

        expect($header)->toBe('Bearer test_installation_token');
    });

    it('refreshes installation token when needed', function () {
        $mockClient = new MockClient([
            MockResponse::make([
                'token' => 'new_installation_token',
                'expires_at' => (new DateTimeImmutable('+1 hour'))->format('c'),
            ]),
        ]);

        $auth = new GitHubAppAuthentication(
            appId: '12345',
            privateKey: $this->privateKey,
            installationId: '67890',
        );

        $connector = new GithubConnector($auth);
        $connector->withMockClient($mockClient);

        expect($auth->needsRefresh())->toBeTrue();

        $auth->refresh();

        expect($auth->needsRefresh())->toBeFalse();
    });

    it('integrates with GithubConnector', function () {
        $auth = new GitHubAppAuthentication(
            appId: '12345',
            privateKey: $this->privateKey,
        );

        $connector = new GithubConnector($auth);

        expect($connector->isAuthenticated())->toBeTrue()
            ->and($connector->getAuthenticationSource())->toBe('github_app');
    });
});

describe('GitHub App Installations Resource', function () {
    beforeEach(function () {
        $this->mockClient = new MockClient();
        $this->privateKey = <<<'KEY'
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA0Z3VS5JJcds3xfn/ygWyF8l0qBnX7l+4MNwMPkN6YODeFbRu
Z0p3AevFKhqiLVT6M3p6wTtQlKJPxjGPMd1YqV0wPG6NNJVFfLNpjt8pSfSiQ7cr
Gw2bqklqjR7lhY0lBF/2YY7v8wWPqfR7FLK7yLNVYC7H6FqN0qMBnJbEbHQk7i0+
uNcm2Ql0cX2XpdjHqKfJ2QmKxKCb8v3HDNNnUU2P+rKxL0FwRLBXRfNRnBBMvVqf
Ll1gxYEGmVGQKWp0pVjJwPTbh8L8fRrSYfLvhNYVPgjNJ9j8UhOCiGfNJDDU4PQp
0SeFZnWPDwGqWVYcNGX5pYHGGqC5pxVJcFNkqQIDAQABAoIBACLQGLXxKiP5N7u+
CY1L5KGmGQCyBpF3YJZfMx+eWRMNT5dTLhE4LPZM5GlKEQ3rqN3nJN7wKQPGsWqG
KEY;

        $auth = new GitHubAppAuthentication(
            appId: '12345',
            privateKey: $this->privateKey,
        );

        $this->github = new Github(new GithubConnector($auth));
        $this->github->connector()->withMockClient($this->mockClient);
    });

    it('can list installations', function () {
        $this->mockClient->addResponse(MockResponse::make([
            [
                'id' => 1,
                'account' => [
                    'login' => 'octocat',
                    'type' => 'User',
                ],
                'target_type' => 'User',
                'permissions' => ['contents' => 'read'],
                'events' => ['push'],
                'created_at' => '2023-01-01T00:00:00Z',
                'updated_at' => '2023-01-02T00:00:00Z',
                'app_slug' => 'my-app',
            ],
        ]));

        $installations = $this->github->installations()->list();

        expect($installations)->toHaveCount(1)
            ->and($installations[0])->toBeInstanceOf(InstallationData::class)
            ->and($installations[0]->id)->toBe(1)
            ->and($installations[0]->account_login)->toBe('octocat');
    });

    it('can get a specific installation', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'id' => 1,
            'account' => [
                'login' => 'octocat',
                'type' => 'User',
            ],
            'target_type' => 'User',
            'permissions' => ['contents' => 'read'],
            'events' => ['push'],
            'created_at' => '2023-01-01T00:00:00Z',
            'updated_at' => '2023-01-02T00:00:00Z',
            'app_slug' => 'my-app',
        ]));

        $installation = $this->github->installations()->get(1);

        expect($installation)->toBeInstanceOf(InstallationData::class)
            ->and($installation->id)->toBe(1)
            ->and($installation->account_login)->toBe('octocat');
    });

    it('can create installation access token', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'token' => 'ghs_installationtoken',
            'expires_at' => '2023-01-01T01:00:00Z',
            'permissions' => ['contents' => 'read'],
            'repository_selection' => 'all',
        ]));

        $token = $this->github->installations()->createAccessToken(1);

        expect($token)->toBeInstanceOf(InstallationTokenData::class)
            ->and($token->token)->toBe('ghs_installationtoken')
            ->and($token->repository_selection)->toBe('all');
    });

    it('handles pagination when listing all installations', function () {
        // First page
        $this->mockClient->addResponse(
            MockResponse::make([
                ['id' => 1, 'account' => ['login' => 'user1', 'type' => 'User']],
                ['id' => 2, 'account' => ['login' => 'user2', 'type' => 'User']],
            ])->withHeader('Link', '<https://api.github.com/installations?page=2>; rel="next"'),
        );

        // Second page
        $this->mockClient->addResponse(
            MockResponse::make([
                ['id' => 3, 'account' => ['login' => 'user3', 'type' => 'User']],
            ]),
        );

        $installations = $this->github->installations()->listAll(2);

        expect($installations)->toHaveCount(3)
            ->and($installations[0]->id)->toBe(1)
            ->and($installations[2]->id)->toBe(3);
    });
});

describe('GitHub App Helper Methods', function () {
    it('creates client for installation', function () {
        config([
            'github-client.github_app.app_id' => '12345',
            'github-client.github_app.private_key' => <<<'KEY'
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA0Z3VS5JJcds3xfn/ygWyF8l0qBnX7l+4MNwMPkN6YODeFbRu
Z0p3AevFKhqiLVT6M3p6wTtQlKJPxjGPMd1YqV0wPG6NNJVFfLNpjt8pSfSiQ7cr
KEY,
        ]);

        $github = Github::forInstallation(67890);

        expect($github)->toBeInstanceOf(Github::class)
            ->and($github->connector()->isAuthenticated())->toBeTrue();
    });

    it('creates client with custom app credentials', function () {
        $privateKey = <<<'KEY'
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA0Z3VS5JJcds3xfn/ygWyF8l0qBnX7l+4MNwMPkN6YODeFbRu
Z0p3AevFKhqiLVT6M3p6wTtQlKJPxjGPMd1YqV0wPG6NNJVFfLNpjt8pSfSiQ7cr
KEY;

        $github = Github::withApp('12345', $privateKey, 67890);

        expect($github)->toBeInstanceOf(Github::class)
            ->and($github->connector()->isAuthenticated())->toBeTrue();
    });

    it('throws exception when app not configured', function () {
        config([
            'github-client.github_app.app_id' => null,
            'github-client.github_app.private_key' => null,
        ]);

        expect(fn () => Github::forInstallation(67890))
            ->toThrow(RuntimeException::class, 'GitHub App not configured');
    });
});

describe('InstallationTokenData', function () {
    it('can check if token is expired', function () {
        $expiredToken = new InstallationTokenData(
            token: 'test_token',
            expires_at: \Carbon\Carbon::now()->subHour(),
        );

        $validToken = new InstallationTokenData(
            token: 'test_token',
            expires_at: \Carbon\Carbon::now()->addHour(),
        );

        expect($expiredToken->isExpired())->toBeTrue()
            ->and($validToken->isExpired())->toBeFalse();
    });

    it('can calculate time until expiry', function () {
        $token = new InstallationTokenData(
            token: 'test_token',
            expires_at: \Carbon\Carbon::now()->addMinutes(30),
        );

        $expiresIn = $token->expiresIn();

        expect($expiresIn)->toBeGreaterThan(1700)
            ->and($expiresIn)->toBeLessThan(1900);
    });

    it('converts to array correctly', function () {
        $token = new InstallationTokenData(
            token: 'test_token',
            expires_at: \Carbon\Carbon::parse('2023-01-01T01:00:00Z'),
            permissions: ['contents' => 'read'],
            repository_selection: 'all',
        );

        $array = $token->toArray();

        expect($array)->toHaveKey('token')
            ->and($array)->toHaveKey('expires_at')
            ->and($array)->toHaveKey('permissions')
            ->and($array['token'])->toBe('test_token');
    });
});
