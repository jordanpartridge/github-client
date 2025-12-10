<?php

use Carbon\Carbon;
use JordanPartridge\GithubClient\Data\Installations\InstallationData;

describe('InstallationData', function () {
    describe('fromArray', function () {
        it('creates instance from minimal API response', function () {
            $data = [
                'id' => 12345,
                'account' => [
                    'login' => 'test-org',
                    'type' => 'Organization',
                ],
            ];

            $installation = InstallationData::fromArray($data);

            expect($installation->id)->toBe(12345)
                ->and($installation->account_login)->toBe('test-org')
                ->and($installation->account_type)->toBe('Organization');
        });

        it('creates instance from full API response', function () {
            $data = [
                'id' => 67890,
                'account' => [
                    'login' => 'test-user',
                    'type' => 'User',
                ],
                'target_type' => 'User',
                'permissions' => ['contents' => 'read', 'issues' => 'write'],
                'events' => ['push', 'pull_request'],
                'created_at' => '2024-01-15T10:30:00Z',
                'updated_at' => '2024-06-20T15:45:00Z',
                'app_slug' => 'my-github-app',
            ];

            $installation = InstallationData::fromArray($data);

            expect($installation->id)->toBe(67890)
                ->and($installation->account_login)->toBe('test-user')
                ->and($installation->account_type)->toBe('User')
                ->and($installation->target_type)->toBe('User')
                ->and($installation->permissions)->toBe(['contents' => 'read', 'issues' => 'write'])
                ->and($installation->events)->toBe(['push', 'pull_request'])
                ->and($installation->created_at)->toBeInstanceOf(Carbon::class)
                ->and($installation->updated_at)->toBeInstanceOf(Carbon::class)
                ->and($installation->app_slug)->toBe('my-github-app');
        });

        it('handles missing account data gracefully', function () {
            $data = [
                'id' => 11111,
                'account' => [],
            ];

            $installation = InstallationData::fromArray($data);

            expect($installation->account_login)->toBe('')
                ->and($installation->account_type)->toBe('');
        });

        it('handles missing optional fields', function () {
            $data = [
                'id' => 22222,
                'account' => ['login' => 'user', 'type' => 'User'],
            ];

            $installation = InstallationData::fromArray($data);

            expect($installation->target_type)->toBeNull()
                ->and($installation->permissions)->toBeNull()
                ->and($installation->events)->toBeNull()
                ->and($installation->created_at)->toBeNull()
                ->and($installation->updated_at)->toBeNull()
                ->and($installation->app_slug)->toBeNull();
        });
    });

    describe('toArray', function () {
        it('converts to array with all fields', function () {
            $installation = new InstallationData(
                id: 12345,
                account_login: 'test-org',
                account_type: 'Organization',
                target_type: 'Organization',
                permissions: ['contents' => 'read'],
                events: ['push'],
                created_at: Carbon::parse('2024-01-01T00:00:00Z'),
                updated_at: Carbon::parse('2024-01-02T00:00:00Z'),
                app_slug: 'test-app',
            );

            $array = $installation->toArray();

            expect($array['id'])->toBe(12345)
                ->and($array['account_login'])->toBe('test-org')
                ->and($array['account_type'])->toBe('Organization')
                ->and($array['target_type'])->toBe('Organization')
                ->and($array['permissions'])->toBe(['contents' => 'read'])
                ->and($array['events'])->toBe(['push'])
                ->and($array['app_slug'])->toBe('test-app');
        });

        it('excludes null values from array', function () {
            $installation = new InstallationData(
                id: 12345,
                account_login: 'test-org',
                account_type: 'Organization',
            );

            $array = $installation->toArray();

            expect($array)->not->toHaveKey('target_type')
                ->and($array)->not->toHaveKey('permissions')
                ->and($array)->not->toHaveKey('events')
                ->and($array)->not->toHaveKey('created_at')
                ->and($array)->not->toHaveKey('updated_at')
                ->and($array)->not->toHaveKey('app_slug');
        });

        it('formats dates as ISO strings', function () {
            $installation = new InstallationData(
                id: 12345,
                account_login: 'test',
                account_type: 'User',
                created_at: Carbon::parse('2024-03-15T10:30:00Z'),
            );

            $array = $installation->toArray();

            expect($array['created_at'])->toContain('2024-03-15');
        });
    });

    describe('constructor', function () {
        it('accepts all parameters', function () {
            $createdAt = Carbon::now();
            $updatedAt = Carbon::now();

            $installation = new InstallationData(
                id: 99999,
                account_login: 'my-account',
                account_type: 'Organization',
                target_type: 'Organization',
                permissions: ['admin' => 'write'],
                events: ['issues', 'pull_request'],
                created_at: $createdAt,
                updated_at: $updatedAt,
                app_slug: 'my-app',
            );

            expect($installation->id)->toBe(99999)
                ->and($installation->account_login)->toBe('my-account')
                ->and($installation->account_type)->toBe('Organization')
                ->and($installation->target_type)->toBe('Organization')
                ->and($installation->permissions)->toBe(['admin' => 'write'])
                ->and($installation->events)->toBe(['issues', 'pull_request'])
                ->and($installation->created_at)->toBe($createdAt)
                ->and($installation->updated_at)->toBe($updatedAt)
                ->and($installation->app_slug)->toBe('my-app');
        });
    });
});
