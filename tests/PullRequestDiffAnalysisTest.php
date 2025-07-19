<?php

use JordanPartridge\GithubClient\Facades\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('Pull Request Diff Analysis', function () {
    beforeEach(function () {
        config(['github-client.token' => 'fake-token']);

        // Mock file data representing different types of changes
        $this->mockFilesResponse = [
            // PHP code file - modified
            [
                'sha' => 'abc123',
                'filename' => 'src/Controllers/UserController.php',
                'status' => 'modified',
                'additions' => 45,
                'deletions' => 12,
                'changes' => 57,
                'blob_url' => 'https://github.com/test/repo/blob/abc123/src/Controllers/UserController.php',
                'raw_url' => 'https://github.com/test/repo/raw/abc123/src/Controllers/UserController.php',
                'contents_url' => 'https://api.github.com/repos/test/repo/contents/src/Controllers/UserController.php',
                'patch' => '@@ -1,4 +1,4 @@...',
            ],
            // Test file - added (large change)
            [
                'sha' => 'def456',
                'filename' => 'tests/Unit/UserControllerTest.php',
                'status' => 'added',
                'additions' => 150,
                'deletions' => 0,
                'changes' => 150,
                'blob_url' => 'https://github.com/test/repo/blob/def456/tests/Unit/UserControllerTest.php',
                'raw_url' => 'https://github.com/test/repo/raw/def456/tests/Unit/UserControllerTest.php',
                'contents_url' => 'https://api.github.com/repos/test/repo/contents/tests/Unit/UserControllerTest.php',
            ],
            // Config file - modified
            [
                'sha' => 'ghi789',
                'filename' => '.env.example',
                'status' => 'modified',
                'additions' => 3,
                'deletions' => 1,
                'changes' => 4,
                'blob_url' => 'https://github.com/test/repo/blob/ghi789/.env.example',
                'raw_url' => 'https://github.com/test/repo/raw/ghi789/.env.example',
                'contents_url' => 'https://api.github.com/repos/test/repo/contents/.env.example',
            ],
            // Documentation file - added
            [
                'sha' => 'jkl012',
                'filename' => 'docs/API.md',
                'status' => 'added',
                'additions' => 80,
                'deletions' => 0,
                'changes' => 80,
                'blob_url' => 'https://github.com/test/repo/blob/jkl012/docs/API.md',
                'raw_url' => 'https://github.com/test/repo/raw/jkl012/docs/API.md',
                'contents_url' => 'https://api.github.com/repos/test/repo/contents/docs/API.md',
            ],
            // JavaScript file - deleted
            [
                'sha' => 'mno345',
                'filename' => 'assets/js/legacy.js',
                'status' => 'removed',
                'additions' => 0,
                'deletions' => 200,
                'changes' => 200,
                'blob_url' => 'https://github.com/test/repo/blob/mno345/assets/js/legacy.js',
                'raw_url' => 'https://github.com/test/repo/raw/mno345/assets/js/legacy.js',
                'contents_url' => 'https://api.github.com/repos/test/repo/contents/assets/js/legacy.js',
            ],
            // File renamed
            [
                'sha' => 'pqr678',
                'filename' => 'src/Services/UserService.php',
                'status' => 'renamed',
                'additions' => 5,
                'deletions' => 5,
                'changes' => 10,
                'blob_url' => 'https://github.com/test/repo/blob/pqr678/src/Services/UserService.php',
                'raw_url' => 'https://github.com/test/repo/raw/pqr678/src/Services/UserService.php',
                'contents_url' => 'https://api.github.com/repos/test/repo/contents/src/Services/UserService.php',
                'previous_filename' => 'src/Services/User.php',
            ],
        ];
    });

    describe('files() method', function () {
        it('fetches all files changed in a PR', function () {
            $mockClient = new MockClient([
                MockResponse::make($this->mockFilesResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $files = Github::pullRequests()->files('owner', 'repo', 42);

            expect($files)->toHaveCount(6)
                ->and($files[0]->filename)->toBe('src/Controllers/UserController.php')
                ->and($files[1]->filename)->toBe('tests/Unit/UserControllerTest.php')
                ->and($files[2]->filename)->toBe('.env.example')
                ->and($files[3]->filename)->toBe('docs/API.md')
                ->and($files[4]->filename)->toBe('assets/js/legacy.js')
                ->and($files[5]->filename)->toBe('src/Services/UserService.php');
        });

        it('correctly identifies file types and statuses', function () {
            $mockClient = new MockClient([
                MockResponse::make($this->mockFilesResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $files = Github::pullRequests()->files('owner', 'repo', 42);

            // PHP code file
            expect($files[0]->getFileType())->toBe('php')
                ->and($files[0]->isModified())->toBeTrue()
                ->and($files[0]->isTestFile())->toBeFalse();

            // Test file
            expect($files[1]->getFileType())->toBe('php')
                ->and($files[1]->isAdded())->toBeTrue()
                ->and($files[1]->isTestFile())->toBeTrue()
                ->and($files[1]->isLargeChange())->toBeTrue();

            // Config file
            expect($files[2]->isConfigFile())->toBeTrue()
                ->and($files[2]->isModified())->toBeTrue();

            // Documentation file
            expect($files[3]->isDocumentationFile())->toBeTrue()
                ->and($files[3]->isAdded())->toBeTrue();

            // Deleted file
            expect($files[4]->isDeleted())->toBeTrue()
                ->and($files[4]->hasOnlyDeletions())->toBeTrue();

            // Renamed file
            expect($files[5]->isRenamed())->toBeTrue()
                ->and($files[5]->previous_filename)->toBe('src/Services/User.php');
        });
    });

    describe('diff() method', function () {
        it('provides comprehensive diff analysis', function () {
            $mockClient = new MockClient([
                MockResponse::make($this->mockFilesResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);

            expect($analysis)->toHaveKeys(['summary', 'categories', 'files', 'analysis_tags']);
        });

        it('calculates summary statistics correctly', function () {
            $mockClient = new MockClient([
                MockResponse::make($this->mockFilesResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);
            $summary = $analysis['summary'];

            expect($summary['total_files'])->toBe(6)
                ->and($summary['total_additions'])->toBe(283) // 45+150+3+80+0+5
                ->and($summary['total_deletions'])->toBe(218) // 12+0+1+0+200+5
                ->and($summary['total_changes'])->toBe(501) // 57+150+4+80+200+10
                ->and($summary['large_changes'])->toBe(2) // Test file (150), Deleted file (200)
                ->and($summary['new_files'])->toBe(2) // Test file, Doc file
                ->and($summary['deleted_files'])->toBe(1) // Legacy JS file
                ->and($summary['modified_files'])->toBe(2) // Controller, Config
                ->and($summary['renamed_files'])->toBe(1); // Service file
        });

        it('categorizes files correctly', function () {
            $mockClient = new MockClient([
                MockResponse::make($this->mockFilesResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);
            $categories = $analysis['categories'];

            // Tests category
            expect($categories['tests'])->toHaveCount(1)
                ->and($categories['tests'][0]->filename)->toBe('tests/Unit/UserControllerTest.php');

            // Config category
            expect($categories['config'])->toHaveCount(1)
                ->and($categories['config'][0]->filename)->toBe('.env.example');

            // Docs category
            expect($categories['docs'])->toHaveCount(1)
                ->and($categories['docs'][0]->filename)->toBe('docs/API.md');

            // Code category (PHP files that aren't tests, JS files)
            expect($categories['code'])->toHaveCount(3) // UserController.php, UserService.php, legacy.js
                ->and(collect($categories['code'])->pluck('filename')->toArray())
                ->toContain('src/Controllers/UserController.php')
                ->toContain('src/Services/UserService.php')
                ->toContain('assets/js/legacy.js');

            // Other category should be empty in this test
            expect($categories['other'])->toHaveCount(0);
        });

        it('extracts unique analysis tags', function () {
            $mockClient = new MockClient([
                MockResponse::make($this->mockFilesResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);
            $tags = $analysis['analysis_tags'];

            expect($tags)->toContain('test')
                ->and($tags)->toContain('config')
                ->and($tags)->toContain('docs')
                ->and($tags)->toContain('php')
                ->and($tags)->toContain('javascript')
                ->and($tags)->toContain('markdown')
                ->and($tags)->toContain('modified')
                ->and($tags)->toContain('added')
                ->and($tags)->toContain('removed')
                ->and($tags)->toContain('renamed')
                ->and($tags)->toContain('large-change')
                ->and($tags)->toContain('only-additions')
                ->and($tags)->toContain('only-deletions');
        });

        it('returns all files in the files array', function () {
            $mockClient = new MockClient([
                MockResponse::make($this->mockFilesResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);

            expect($analysis['files'])->toHaveCount(6)
                ->and($analysis['files'][0]->filename)->toBe('src/Controllers/UserController.php');
        });
    });

    describe('Real-world scenarios', function () {
        it('handles empty pull request (no file changes)', function () {
            $mockClient = new MockClient([
                MockResponse::make([], 200), // Empty response
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);

            expect($analysis['summary']['total_files'])->toBe(0)
                ->and($analysis['summary']['total_additions'])->toBe(0)
                ->and($analysis['summary']['total_deletions'])->toBe(0)
                ->and($analysis['categories']['tests'])->toHaveCount(0)
                ->and($analysis['categories']['code'])->toHaveCount(0)
                ->and($analysis['files'])->toHaveCount(0);
        });

        it('handles PR with only test files', function () {
            $testOnlyResponse = [
                [
                    'sha' => 'test123',
                    'filename' => 'tests/Feature/AuthTest.php',
                    'status' => 'added',
                    'additions' => 50,
                    'deletions' => 0,
                    'changes' => 50,
                    'blob_url' => 'https://github.com/test/repo/blob/test123/tests/Feature/AuthTest.php',
                    'raw_url' => 'https://github.com/test/repo/raw/test123/tests/Feature/AuthTest.php',
                    'contents_url' => 'https://api.github.com/repos/test/repo/contents/tests/Feature/AuthTest.php',
                ],
            ];

            $mockClient = new MockClient([
                MockResponse::make($testOnlyResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);

            expect($analysis['summary']['total_files'])->toBe(1)
                ->and($analysis['categories']['tests'])->toHaveCount(1)
                ->and($analysis['categories']['code'])->toHaveCount(0)
                ->and($analysis['analysis_tags'])->toContain('test')
                ->and($analysis['analysis_tags'])->toContain('only-additions');
        });

        it('handles PR with only configuration changes', function () {
            $configOnlyResponse = [
                [
                    'sha' => 'config123',
                    'filename' => 'composer.json',
                    'status' => 'modified',
                    'additions' => 2,
                    'deletions' => 1,
                    'changes' => 3,
                    'blob_url' => 'https://github.com/test/repo/blob/config123/composer.json',
                    'raw_url' => 'https://github.com/test/repo/raw/config123/composer.json',
                    'contents_url' => 'https://api.github.com/repos/test/repo/contents/composer.json',
                ],
                [
                    'sha' => 'env123',
                    'filename' => '.gitignore',
                    'status' => 'modified',
                    'additions' => 1,
                    'deletions' => 0,
                    'changes' => 1,
                    'blob_url' => 'https://github.com/test/repo/blob/env123/.gitignore',
                    'raw_url' => 'https://github.com/test/repo/raw/env123/.gitignore',
                    'contents_url' => 'https://api.github.com/repos/test/repo/contents/.gitignore',
                ],
            ];

            $mockClient = new MockClient([
                MockResponse::make($configOnlyResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);

            expect($analysis['summary']['total_files'])->toBe(2)
                ->and($analysis['summary']['large_changes'])->toBe(0)
                ->and($analysis['categories']['config'])->toHaveCount(0) // composer.json is not detected as config
                ->and($analysis['categories']['other'])->toHaveCount(2) // Both files are 'other'
                ->and($analysis['analysis_tags'])->toContain('modified')
                ->and($analysis['analysis_tags'])->toContain('json');
        });

        it('handles large PR with mixed file types', function () {
            $this->mockFilesResponse[] = [
                'sha' => 'extra1',
                'filename' => 'src/Models/User.php',
                'status' => 'modified',
                'additions' => 25,
                'deletions' => 10,
                'changes' => 35,
                'blob_url' => 'https://github.com/test/repo/blob/extra1/src/Models/User.php',
                'raw_url' => 'https://github.com/test/repo/raw/extra1/src/Models/User.php',
                'contents_url' => 'https://api.github.com/repos/test/repo/contents/src/Models/User.php',
            ];

            $mockClient = new MockClient([
                MockResponse::make($this->mockFilesResponse, 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $analysis = Github::pullRequests()->diff('owner', 'repo', 42);

            expect($analysis['summary']['total_files'])->toBe(7)
                ->and($analysis['categories']['code'])->toHaveCount(4) // UserController, UserService, legacy.js, User.php
                ->and($analysis['categories']['tests'])->toHaveCount(1)
                ->and($analysis['categories']['config'])->toHaveCount(1)
                ->and($analysis['categories']['docs'])->toHaveCount(1);
        });
    });

    describe('Error handling', function () {
        it('handles API errors gracefully', function () {
            $mockClient = new MockClient([
                MockResponse::make(['message' => 'Not Found'], 404),
            ]);

            Github::connector()->withMockClient($mockClient);

            expect(fn () => Github::pullRequests()->files('owner', 'repo', 999))
                ->toThrow(TypeError::class);
        });
    });
});
