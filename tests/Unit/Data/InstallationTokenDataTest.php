<?php

use Carbon\Carbon;
use JordanPartridge\GithubClient\Data\Installations\InstallationTokenData;

describe('InstallationTokenData', function () {
    describe('fromArray', function () {
        it('creates instance from API response', function () {
            $data = [
                'token' => 'ghs_xxxxxxxxxxxxxxxxxxxx',
                'expires_at' => '2024-01-15T12:00:00Z',
            ];

            $tokenData = InstallationTokenData::fromArray($data);

            expect($tokenData->token)->toBe('ghs_xxxxxxxxxxxxxxxxxxxx')
                ->and($tokenData->expires_at)->toBeInstanceOf(Carbon::class);
        });

        it('creates instance with all fields', function () {
            $data = [
                'token' => 'ghs_test_token',
                'expires_at' => '2024-06-01T15:30:00Z',
                'permissions' => ['contents' => 'read', 'issues' => 'write'],
                'repository_selection' => 'selected',
            ];

            $tokenData = InstallationTokenData::fromArray($data);

            expect($tokenData->token)->toBe('ghs_test_token')
                ->and($tokenData->permissions)->toBe(['contents' => 'read', 'issues' => 'write'])
                ->and($tokenData->repository_selection)->toBe('selected');
        });

        it('handles missing optional fields', function () {
            $data = [
                'token' => 'ghs_minimal',
                'expires_at' => '2024-01-01T00:00:00Z',
            ];

            $tokenData = InstallationTokenData::fromArray($data);

            expect($tokenData->permissions)->toBeNull()
                ->and($tokenData->repository_selection)->toBeNull();
        });
    });

    describe('toArray', function () {
        it('converts to array with all fields', function () {
            $tokenData = new InstallationTokenData(
                token: 'ghs_full_token',
                expires_at: Carbon::parse('2024-03-15T10:00:00Z'),
                permissions: ['contents' => 'write'],
                repository_selection: 'all',
            );

            $array = $tokenData->toArray();

            expect($array['token'])->toBe('ghs_full_token')
                ->and($array['expires_at'])->toContain('2024-03-15')
                ->and($array['permissions'])->toBe(['contents' => 'write'])
                ->and($array['repository_selection'])->toBe('all');
        });

        it('excludes null values', function () {
            $tokenData = new InstallationTokenData(
                token: 'ghs_basic',
                expires_at: Carbon::now()->addHour(),
            );

            $array = $tokenData->toArray();

            expect($array)->not->toHaveKey('permissions')
                ->and($array)->not->toHaveKey('repository_selection');
        });
    });

    describe('isExpired', function () {
        it('returns false for future expiry', function () {
            $tokenData = new InstallationTokenData(
                token: 'ghs_valid',
                expires_at: Carbon::now()->addHour(),
            );

            expect($tokenData->isExpired())->toBeFalse();
        });

        it('returns true for past expiry', function () {
            $tokenData = new InstallationTokenData(
                token: 'ghs_expired',
                expires_at: Carbon::now()->subHour(),
            );

            expect($tokenData->isExpired())->toBeTrue();
        });

        it('returns true for exactly now', function () {
            $tokenData = new InstallationTokenData(
                token: 'ghs_now',
                expires_at: Carbon::now(),
            );

            expect($tokenData->isExpired())->toBeTrue();
        });
    });

    describe('expiresIn', function () {
        it('returns positive seconds for future expiry', function () {
            $tokenData = new InstallationTokenData(
                token: 'ghs_future',
                expires_at: Carbon::now()->addMinutes(30),
            );

            $expiresIn = $tokenData->expiresIn();

            // Should be approximately 30 minutes in seconds (1800)
            expect($expiresIn)->toBeGreaterThan(1700)
                ->and($expiresIn)->toBeLessThan(1900);
        });

        it('returns negative seconds for past expiry', function () {
            $tokenData = new InstallationTokenData(
                token: 'ghs_past',
                expires_at: Carbon::now()->subMinutes(10),
            );

            $expiresIn = $tokenData->expiresIn();

            // Should be approximately -600 seconds
            expect($expiresIn)->toBeLessThan(0)
                ->and($expiresIn)->toBeGreaterThan(-700);
        });

        it('returns approximately zero for now', function () {
            $tokenData = new InstallationTokenData(
                token: 'ghs_now',
                expires_at: Carbon::now(),
            );

            $expiresIn = $tokenData->expiresIn();

            // Should be very close to 0
            expect(abs($expiresIn))->toBeLessThan(5);
        });
    });

    describe('constructor', function () {
        it('requires token and expires_at', function () {
            $expiresAt = Carbon::now()->addHour();
            $tokenData = new InstallationTokenData(
                token: 'ghs_required',
                expires_at: $expiresAt,
            );

            expect($tokenData->token)->toBe('ghs_required')
                ->and($tokenData->expires_at)->toBe($expiresAt);
        });

        it('accepts all parameters', function () {
            $expiresAt = Carbon::now()->addHours(2);
            $tokenData = new InstallationTokenData(
                token: 'ghs_complete',
                expires_at: $expiresAt,
                permissions: ['metadata' => 'read'],
                repository_selection: 'selected',
            );

            expect($tokenData->token)->toBe('ghs_complete')
                ->and($tokenData->expires_at)->toBe($expiresAt)
                ->and($tokenData->permissions)->toBe(['metadata' => 'read'])
                ->and($tokenData->repository_selection)->toBe('selected');
        });
    });
});
