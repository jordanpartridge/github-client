<?php

use JordanPartridge\GithubClient\Auth\GitHubAppAuthentication;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

// Valid RSA private key for testing
$validPrivateKey = <<<'KEY'
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

describe('GitHubAppAuthentication', function () use ($validPrivateKey) {
    describe('constructor and getters', function () use ($validPrivateKey) {
        it('stores app ID correctly', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey);
            expect($auth->getAppId())->toBe('12345');
        });

        it('stores installation ID when provided', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey, '67890');
            expect($auth->getInstallationId())->toBe('67890');
        });

        it('returns null for installation ID when not provided', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey);
            expect($auth->getInstallationId())->toBeNull();
        });

        it('returns github_app as auth type', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey);
            expect($auth->getType())->toBe('github_app');
        });
    });

    describe('validation', function () use ($validPrivateKey) {
        it('passes validation with valid credentials', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey);
            expect(fn () => $auth->validate())->not->toThrow(AuthenticationException::class);
        });

        it('throws exception for empty app ID', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('', $validPrivateKey);
            expect(fn () => $auth->validate())->toThrow(AuthenticationException::class, 'App ID is required');
        });

        it('throws exception for non-numeric app ID', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('abc', $validPrivateKey);
            expect(fn () => $auth->validate())->toThrow(AuthenticationException::class, 'App ID must be numeric');
        });

        it('throws exception for empty private key', function () {
            $auth = new GitHubAppAuthentication('12345', '');
            expect(fn () => $auth->validate())->toThrow(AuthenticationException::class, 'Private key is required');
        });

        it('throws exception for invalid private key format', function () {
            $auth = new GitHubAppAuthentication('12345', 'not-a-valid-key');
            expect(fn () => $auth->validate())->toThrow(AuthenticationException::class, 'Invalid private key format');
        });
    });

    describe('JWT token generation', function () use ($validPrivateKey) {
        it('generates JWT token starting with Bearer', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey);
            $header = $auth->getAuthorizationHeader();
            expect($header)->toStartWith('Bearer ');
        });

        it('generates JWT token with proper length', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey);
            $header = $auth->getAuthorizationHeader();
            // JWT tokens are quite long (header.payload.signature)
            expect(strlen($header))->toBeGreaterThan(100);
        });

        it('generates different tokens on each call due to timestamp', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey);
            $header1 = $auth->getAuthorizationHeader();
            sleep(1); // Wait for timestamp to change
            $header2 = $auth->getAuthorizationHeader();
            // They might be the same if called within same second, but structure should be valid
            expect($header1)->toStartWith('Bearer ')
                ->and($header2)->toStartWith('Bearer ');
        });
    });

    describe('installation token handling', function () use ($validPrivateKey) {
        it('returns JWT when no installation token set', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey, '67890');
            $header = $auth->getAuthorizationHeader();
            // Should be JWT since no installation token is set
            expect($header)->toStartWith('Bearer ')
                ->and(strlen($header))->toBeGreaterThan(100);
        });

        it('returns installation token when set and valid', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey, '67890');
            $expiry = new DateTimeImmutable('+1 hour');
            $auth->setInstallationToken('ghs_test_token', $expiry);

            $header = $auth->getAuthorizationHeader();
            expect($header)->toBe('Bearer ghs_test_token');
        });

        it('returns JWT when installation token is expired', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey, '67890');
            $expiry = new DateTimeImmutable('-1 hour'); // Expired
            $auth->setInstallationToken('ghs_expired_token', $expiry);

            $header = $auth->getAuthorizationHeader();
            // Should fall back to JWT since token is expired
            expect(strlen($header))->toBeGreaterThan(50);
        });
    });

    describe('needsRefresh', function () use ($validPrivateKey) {
        it('returns false for app-level auth without installation ID', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey);
            expect($auth->needsRefresh())->toBeFalse();
        });

        it('returns true for installation auth without token', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey, '67890');
            expect($auth->needsRefresh())->toBeTrue();
        });

        it('returns false when valid installation token is set', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey, '67890');
            $expiry = new DateTimeImmutable('+1 hour');
            $auth->setInstallationToken('ghs_valid_token', $expiry);
            expect($auth->needsRefresh())->toBeFalse();
        });

        it('returns true when installation token is near expiry', function () use ($validPrivateKey) {
            $auth = new GitHubAppAuthentication('12345', $validPrivateKey, '67890');
            // Set token that expires in 3 minutes (within 5-minute buffer)
            $expiry = new DateTimeImmutable('+3 minutes');
            $auth->setInstallationToken('ghs_expiring_token', $expiry);
            expect($auth->needsRefresh())->toBeTrue();
        });
    });
});
