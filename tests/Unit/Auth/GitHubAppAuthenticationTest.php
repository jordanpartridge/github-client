<?php

use JordanPartridge\GithubClient\Auth\GitHubAppAuthentication;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

// Helper function to generate test RSA private key
function generateTestPrivateKey(): string
{
    $config = [
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];
    $key = openssl_pkey_new($config);
    openssl_pkey_export($key, $privateKey);

    return $privateKey;
}

describe('GitHubAppAuthentication', function () {
    describe('validate', function () {
        it('throws exception for empty app ID', function () {
            $auth = new GitHubAppAuthentication(
                appId: '',
                privateKey: 'test-key',
            );

            expect(fn () => $auth->validate())
                ->toThrow(AuthenticationException::class, 'App ID is required');
        });

        it('throws exception for non-numeric app ID', function () {
            $auth = new GitHubAppAuthentication(
                appId: 'not-a-number',
                privateKey: 'test-key',
            );

            expect(fn () => $auth->validate())
                ->toThrow(AuthenticationException::class, 'App ID must be numeric');
        });

        it('throws exception for empty private key', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: '',
            );

            expect(fn () => $auth->validate())
                ->toThrow(AuthenticationException::class, 'Private key is required');
        });

        it('throws exception for invalid private key format', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: 'this-is-not-a-valid-pem-key',
            );

            expect(fn () => $auth->validate())
                ->toThrow(AuthenticationException::class, 'Invalid private key format');
        });

        it('validates with valid PEM private key', function () {
            $privateKey = generateTestPrivateKey();

            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: $privateKey,
            );

            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });

        it('validates with base64 encoded private key', function () {
            $privateKey = generateTestPrivateKey();
            $base64Key = base64_encode($privateKey);

            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: $base64Key,
            );

            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });
    });

    describe('getType', function () {
        it('returns github_app type', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
            );

            expect($auth->getType())->toBe('github_app');
        });
    });

    describe('getAuthorizationHeader', function () {
        it('returns JWT bearer token', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
            );

            $header = $auth->getAuthorizationHeader();

            expect($header)->toStartWith('Bearer ');

            // Extract and decode JWT
            $token = substr($header, 7);
            $parts = explode('.', $token);
            expect($parts)->toHaveCount(3); // JWT has 3 parts: header.payload.signature

            // Decode payload
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            expect($payload['iss'])->toBe('12345')
                ->and($payload)->toHaveKey('iat')
                ->and($payload)->toHaveKey('exp')
                ->and($payload['exp'] - $payload['iat'])->toBe(600); // 10 minutes
        });

        it('returns installation token when set', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            // Set a valid installation token that expires in the future
            $expiry = new DateTimeImmutable('+1 hour');
            $auth->setInstallationToken('ghs_installation_token_12345', $expiry);

            $header = $auth->getAuthorizationHeader();

            expect($header)->toBe('Bearer ghs_installation_token_12345');
        });

        it('returns JWT when installation token is expired', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            // Set an expired installation token (expired 1 hour ago)
            $expiry = new DateTimeImmutable('-1 hour');
            $auth->setInstallationToken('ghs_expired_token', $expiry);

            $header = $auth->getAuthorizationHeader();

            // Should return JWT, not the expired installation token
            expect($header)->toStartWith('Bearer ')
                ->and($header)->not->toContain('ghs_expired_token');
        });

        it('returns JWT when installation token is about to expire within buffer', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            // Set installation token that expires in 4 minutes (within 5-minute buffer)
            $expiry = new DateTimeImmutable('+4 minutes');
            $auth->setInstallationToken('ghs_soon_expired_token', $expiry);

            $header = $auth->getAuthorizationHeader();

            // Should return JWT since token is about to expire
            expect($header)->toStartWith('Bearer ')
                ->and($header)->not->toContain('ghs_soon_expired_token');
        });
    });

    describe('needsRefresh', function () {
        it('returns false when no installation ID is set', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
            );

            expect($auth->needsRefresh())->toBeFalse();
        });

        it('returns true when installation ID is set but no token', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            expect($auth->needsRefresh())->toBeTrue();
        });

        it('returns false when valid installation token is set', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            $expiry = new DateTimeImmutable('+1 hour');
            $auth->setInstallationToken('ghs_valid_token', $expiry);

            expect($auth->needsRefresh())->toBeFalse();
        });

        it('returns true when installation token is expired', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            $expiry = new DateTimeImmutable('-1 hour');
            $auth->setInstallationToken('ghs_expired_token', $expiry);

            expect($auth->needsRefresh())->toBeTrue();
        });
    });

    describe('refresh', function () {
        it('does nothing when no installation ID is set', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
            );

            // Should not throw
            $auth->refresh();

            expect(true)->toBeTrue();
        });

        it('throws exception when installation ID is set', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            expect(fn () => $auth->refresh())
                ->toThrow(AuthenticationException::class, 'Installation token refresh not yet implemented');
        });
    });

    describe('setInstallationToken', function () {
        it('sets the installation token and expiry', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            $expiry = new DateTimeImmutable('+1 hour');
            $auth->setInstallationToken('ghs_my_token', $expiry);

            $header = $auth->getAuthorizationHeader();
            expect($header)->toBe('Bearer ghs_my_token');
        });
    });

    describe('getAppId', function () {
        it('returns the app ID', function () {
            $auth = new GitHubAppAuthentication(
                appId: '98765',
                privateKey: generateTestPrivateKey(),
            );

            expect($auth->getAppId())->toBe('98765');
        });
    });

    describe('getInstallationId', function () {
        it('returns null when no installation ID is set', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
            );

            expect($auth->getInstallationId())->toBeNull();
        });

        it('returns the installation ID when set', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
                installationId: '67890',
            );

            expect($auth->getInstallationId())->toBe('67890');
        });
    });

    describe('implements AuthenticationStrategy', function () {
        it('implements the interface', function () {
            $auth = new GitHubAppAuthentication(
                appId: '12345',
                privateKey: generateTestPrivateKey(),
            );

            expect($auth)->toBeInstanceOf(\JordanPartridge\GithubClient\Auth\AuthenticationStrategy::class);
        });
    });
});
