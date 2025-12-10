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
        // Valid RSA private key for testing
        $this->privateKey = <<<'KEY'
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAslfQS6vMWuJJ0efHU597lO4kLfrGz6b/7TDxfi8h/6zH9Kwj
qJJGEKcWRM58v5CtOZVVW1EGVBrCisRim8MgyYpyu9vGHJ7WU0O+tdcGNgdMQg5o
+kjztalRg/SrN4h3v7WRpGltc7zKiKfxpOKX73qND2v1GUuvRRjMr6kbxYON46Po
y0xUCA76XGpeCLhndycSeGXJGKtGyskdQVQz+bTebKRw7T2K76ZSzejDZ2clUszG
GArDGm6fOkyQqcLYFagH6+Ij00eEa4oc+ko6r8ssdNXq6mAfLe+/+mImzJgLjkkI
W/QSa168ZXLNrHd0iBxtzYgfOjXOEvD9+fnAHQIDAQABAoIBAEZaoPexEsFJoqB1
RGtHL2vdlBU1aEeTlEOAOswyEMH5aboqTDjcV1qLZ5wGLy0tCED+mbkX2hzEn7k5
ZsMWs3D+NmvIc7tnp5oaT/oRFMiskVc/swcFT2r7HAantwGFyuOsX4OR+ZUeRXGq
ewe2QgS9fjc2ue5cLozeDyU+249LQMbVY8fI7E3GyVJJDelGSnoKXzFXrzE2H7BQ
sRv8Fbon7MkMHE0OZyj84q/GKR+NtOUMIXfj2c2mzO6FIZaWI57qcj6goxuZhAAW
KOQ553Cf1eTjU0Yjc0oFZC2oh6gd3QpA/jCZpqUvE/IfOA69lpdXzb61+7RWNNIH
TC0+/msCgYEA53i2bjaYDegdfl47NxMSbAoa9r/Rwb/ym4Dr2oqaXM0l1QFVwEVn
OOXKdDQS49UDnWD/IldRfr8tHIcFn2qscQkWrMSsw0hD6POmyP7zlAPd850bkTWt
CLhlv0utrg6ZmlWYqgFbks3QUHS9M9AscdJgD8kwKiSM21mwvwU1J38CgYEAxT3Y
7mCh/notyUbisT3ww5xAWoWhBQufJdcw5zF6fFsgR3XI4GvD116LeVE4lPfri3E+
/LJlujHPbew/Ul4Wl/RK4d+VfPCAvLpabcwbs//T/JtflmpBJtUQ+qWO9wPqk9FT
vw4t0ofytRBtuxyVvL+VlyGaARKcbPItFCPBhmMCgYEA4upUtVDx2Vg+aZ6JIGGj
AqUZb+H2CKFafZVyIYkU8HrwZpNrdBTVr1KeGTLffdhaNdNb6ld9fep+l+PJ4FEc
AafuQaqAzuQuJtWNIKfHM8hisqrG1qCvI8hZfqH6/pIzhLf69FZmZlE7sVPwEzY2
C9M39uG9ROMV7wdLHMhHJpMCgYEAq/8ztSNFAAEQ/iy3L7IAysLth0Jx2FF7FWdi
eKi308svCcGXSsQOgjcqzr7Z5WWP3AgD0h3LAaO/624RBcWQVC+uQOtUkx+yU2D5
zDcpjTwwYl4m66Z6a99ur/NBCPw8SWxHaBp4MNdl+Sh7V6gklvRGAQVHI1pUV9iT
ILXRY1sCgYALvrX0RqVWH9k2A/QmuVOZ91FHeLh5oGhpy9S1MrlkR1DAF+nqt21f
eWypcCrvYCDf4yUkWR9iVhyL+nZkqfLSBymPBf/YCUC7JV8PsKjacdbfMpx/j44i
xUYDxU/UfWEaoKm4gKbiRLMthpdXJTY26KTDP01KG8qVuiJX1PX6/g==
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
MIIEpAIBAAKCAQEAslfQS6vMWuJJ0efHU597lO4kLfrGz6b/7TDxfi8h/6zH9Kwj
qJJGEKcWRM58v5CtOZVVW1EGVBrCisRim8MgyYpyu9vGHJ7WU0O+tdcGNgdMQg5o
+kjztalRg/SrN4h3v7WRpGltc7zKiKfxpOKX73qND2v1GUuvRRjMr6kbxYON46Po
y0xUCA76XGpeCLhndycSeGXJGKtGyskdQVQz+bTebKRw7T2K76ZSzejDZ2clUszG
GArDGm6fOkyQqcLYFagH6+Ij00eEa4oc+ko6r8ssdNXq6mAfLe+/+mImzJgLjkkI
W/QSa168ZXLNrHd0iBxtzYgfOjXOEvD9+fnAHQIDAQABAoIBAEZaoPexEsFJoqB1
RGtHL2vdlBU1aEeTlEOAOswyEMH5aboqTDjcV1qLZ5wGLy0tCED+mbkX2hzEn7k5
ZsMWs3D+NmvIc7tnp5oaT/oRFMiskVc/swcFT2r7HAantwGFyuOsX4OR+ZUeRXGq
ewe2QgS9fjc2ue5cLozeDyU+249LQMbVY8fI7E3GyVJJDelGSnoKXzFXrzE2H7BQ
sRv8Fbon7MkMHE0OZyj84q/GKR+NtOUMIXfj2c2mzO6FIZaWI57qcj6goxuZhAAW
KOQ553Cf1eTjU0Yjc0oFZC2oh6gd3QpA/jCZpqUvE/IfOA69lpdXzb61+7RWNNIH
TC0+/msCgYEA53i2bjaYDegdfl47NxMSbAoa9r/Rwb/ym4Dr2oqaXM0l1QFVwEVn
OOXKdDQS49UDnWD/IldRfr8tHIcFn2qscQkWrMSsw0hD6POmyP7zlAPd850bkTWt
CLhlv0utrg6ZmlWYqgFbks3QUHS9M9AscdJgD8kwKiSM21mwvwU1J38CgYEAxT3Y
7mCh/notyUbisT3ww5xAWoWhBQufJdcw5zF6fFsgR3XI4GvD116LeVE4lPfri3E+
/LJlujHPbew/Ul4Wl/RK4d+VfPCAvLpabcwbs//T/JtflmpBJtUQ+qWO9wPqk9FT
vw4t0ofytRBtuxyVvL+VlyGaARKcbPItFCPBhmMCgYEA4upUtVDx2Vg+aZ6JIGGj
AqUZb+H2CKFafZVyIYkU8HrwZpNrdBTVr1KeGTLffdhaNdNb6ld9fep+l+PJ4FEc
AafuQaqAzuQuJtWNIKfHM8hisqrG1qCvI8hZfqH6/pIzhLf69FZmZlE7sVPwEzY2
C9M39uG9ROMV7wdLHMhHJpMCgYEAq/8ztSNFAAEQ/iy3L7IAysLth0Jx2FF7FWdi
eKi308svCcGXSsQOgjcqzr7Z5WWP3AgD0h3LAaO/624RBcWQVC+uQOtUkx+yU2D5
zDcpjTwwYl4m66Z6a99ur/NBCPw8SWxHaBp4MNdl+Sh7V6gklvRGAQVHI1pUV9iT
ILXRY1sCgYALvrX0RqVWH9k2A/QmuVOZ91FHeLh5oGhpy9S1MrlkR1DAF+nqt21f
eWypcCrvYCDf4yUkWR9iVhyL+nZkqfLSBymPBf/YCUC7JV8PsKjacdbfMpx/j44i
xUYDxU/UfWEaoKm4gKbiRLMthpdXJTY26KTDP01KG8qVuiJX1PX6/g==
-----END RSA PRIVATE KEY-----
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
