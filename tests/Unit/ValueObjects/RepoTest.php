<?php

use JordanPartridge\GithubClient\ValueObjects\Repo;

describe('Repo ValueObject', function () {
    describe('fromFullName method', function () {
        it('creates repo from valid full name', function () {
            $repo = Repo::fromFullName('owner/repository');

            expect($repo->owner())->toBe('owner')
                ->and($repo->name())->toBe('repository')
                ->and($repo->fullName())->toBe('owner/repository');
        });

        it('throws exception for invalid format', function () {
            expect(fn () => Repo::fromFullName('invalid'))
                ->toThrow(\\InvalidArgumentException::class, 'Repository must be in format "owner/repo"');
        });

        it('throws exception for empty owner or name', function () {
            expect(fn () => Repo::fromFullName('/repository'))
                ->toThrow(\InvalidArgumentException::class, 'Owner and repo name cannot be empty.');

            expect(fn () => Repo::fromFullName('owner/'))
                ->toThrow(\InvalidArgumentException::class, 'Owner and repo name cannot be empty.');
        });

        it('throws exception for invalid characters', function () {
            expect(fn () => Repo::fromFullName('owner@invalid/repository'))
                ->toThrow(\InvalidArgumentException::class, "Invalid characters in repository name 'owner@invalid/repository'.");
        });

        it('accepts valid characters (letters, numbers, dots, underscores, hyphens)', function () {
            $repo = Repo::fromFullName('owner-123/repo_name.test');

            expect($repo->owner())->toBe('owner-123')
                ->and($repo->name())->toBe('repo_name.test');
        });
    });

    describe('fromOwnerAndRepo method', function () {
        it('creates repo from separate owner and repo parameters', function () {
            $repo = Repo::fromOwnerAndRepo('testowner', 'testrepository');

            expect($repo->owner())->toBe('testowner')
                ->and($repo->name())->toBe('testrepository')
                ->and($repo->fullName())->toBe('testowner/testrepository');
        });

        it('throws exception for empty owner', function () {
            expect(fn () => Repo::fromOwnerAndRepo('', 'repository'))
                ->toThrow(\InvalidArgumentException::class, 'Owner cannot be empty.');
        });

        it('throws exception for empty repository name', function () {
            expect(fn () => Repo::fromOwnerAndRepo('owner', ''))
                ->toThrow(\InvalidArgumentException::class, 'Repository name cannot be empty.');
        });

        it('throws exception for invalid characters in owner', function () {
            expect(fn () => Repo::fromOwnerAndRepo('owner@invalid', 'repository'))
                ->toThrow(\InvalidArgumentException::class, "Invalid characters in owner name 'owner@invalid'.");
        });

        it('throws exception for invalid characters in repository name', function () {
            expect(fn () => Repo::fromOwnerAndRepo('owner', 'repo@invalid'))
                ->toThrow(\InvalidArgumentException::class, "Invalid characters in repository name 'repo@invalid'.");
        });

        it('accepts valid characters in both parameters', function () {
            $repo = Repo::fromOwnerAndRepo('owner-123', 'repo_name.test-2');

            expect($repo->owner())->toBe('owner-123')
                ->and($repo->name())->toBe('repo_name.test-2')
                ->and($repo->fullName())->toBe('owner-123/repo_name.test-2');
        });

        it('validates the same way as fromFullName', function () {
            $repo1 = Repo::fromFullName('owner-test/repo-test.name');
            $repo2 = Repo::fromOwnerAndRepo('owner-test', 'repo-test.name');

            expect($repo1->owner())->toBe($repo2->owner())
                ->and($repo1->name())->toBe($repo2->name())
                ->and($repo1->fullName())->toBe($repo2->fullName());
        });
    });

    describe('fromRepo method', function () {
        it('creates a copy from another repo instance', function () {
            $original = Repo::fromFullName('original/repository');
            $copy = Repo::fromRepo($original);

            expect($copy->owner())->toBe($original->owner())
                ->and($copy->name())->toBe($original->name())
                ->and($copy->fullName())->toBe($original->fullName());
        });

        it('creates independent instances', function () {
            $repo1 = Repo::fromFullName('owner/repo');
            $repo2 = Repo::fromRepo($repo1);

            // They should have the same values but be different objects
            expect($repo1)->not->toBe($repo2)
                ->and($repo1->fullName())->toBe($repo2->fullName());
        });
    });

    describe('accessor methods', function () {
        it('returns correct owner and name', function () {
            $repo = Repo::fromFullName('my-org/awesome-project');

            expect($repo->owner())->toBe('my-org')
                ->and($repo->name())->toBe('awesome-project');
        });

        it('returns correct full name', function () {
            $repo = Repo::fromOwnerAndRepo('github', 'docs');

            expect($repo->fullName())->toBe('github/docs');
        });
    });

    describe('edge cases', function () {
        it('handles numeric owner and repository names', function () {
            $repo = Repo::fromOwnerAndRepo('123', '456');

            expect($repo->owner())->toBe('123')
                ->and($repo->name())->toBe('456')
                ->and($repo->fullName())->toBe('123/456');
        });

        it('handles mixed alphanumeric with allowed special characters', function () {
            $repo = Repo::fromFullName('user-123.test/repo_name-2024.js');

            expect($repo->owner())->toBe('user-123.test')
                ->and($repo->name())->toBe('repo_name-2024.js');
        });
    });
});
