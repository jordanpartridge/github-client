<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestFileDTO;

describe('Pull Request Files API', function () {
    beforeEach(function () {
        $this->mockFileData = [
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
        ];

        $this->mockTestFileData = [
            'sha' => 'def456',
            'filename' => 'tests/Unit/UserControllerTest.php',
            'status' => 'added',
            'additions' => 120,
            'deletions' => 0,
            'changes' => 120,
            'blob_url' => 'https://github.com/test/repo/blob/def456/tests/Unit/UserControllerTest.php',
            'raw_url' => 'https://github.com/test/repo/raw/def456/tests/Unit/UserControllerTest.php',
            'contents_url' => 'https://api.github.com/repos/test/repo/contents/tests/Unit/UserControllerTest.php',
        ];

        $this->mockConfigFileData = [
            'sha' => 'ghi789',
            'filename' => '.env.example',
            'status' => 'modified',
            'additions' => 3,
            'deletions' => 1,
            'changes' => 4,
            'blob_url' => 'https://github.com/test/repo/blob/ghi789/.env.example',
            'raw_url' => 'https://github.com/test/repo/raw/ghi789/.env.example',
            'contents_url' => 'https://api.github.com/repos/test/repo/contents/.env.example',
        ];
    });

    describe('PullRequestFileDTO', function () {
        it('creates from API response correctly', function () {
            $dto = PullRequestFileDTO::fromApiResponse($this->mockFileData);

            expect($dto->sha)->toBe('abc123')
                ->and($dto->filename)->toBe('src/Controllers/UserController.php')
                ->and($dto->status)->toBe('modified')
                ->and($dto->additions)->toBe(45)
                ->and($dto->deletions)->toBe(12)
                ->and($dto->changes)->toBe(57)
                ->and($dto->patch)->toBe('@@ -1,4 +1,4 @@...');
        });

        it('converts to array representation', function () {
            $dto = PullRequestFileDTO::fromApiResponse($this->mockFileData);
            $array = $dto->toArray();

            expect($array)->toHaveKey('sha', 'abc123')
                ->and($array)->toHaveKey('filename', 'src/Controllers/UserController.php')
                ->and($array)->toHaveKey('additions', 45)
                ->and($array)->toHaveKey('deletions', 12);
        });

        describe('Status Methods', function () {
            it('correctly identifies file status', function () {
                $modifiedFile = PullRequestFileDTO::fromApiResponse($this->mockFileData);
                $addedFile = PullRequestFileDTO::fromApiResponse($this->mockTestFileData);

                $deletedFileData = array_merge($this->mockFileData, ['status' => 'removed']);
                $deletedFile = PullRequestFileDTO::fromApiResponse($deletedFileData);

                $renamedFileData = array_merge($this->mockFileData, ['status' => 'renamed']);
                $renamedFile = PullRequestFileDTO::fromApiResponse($renamedFileData);

                expect($modifiedFile->isModified())->toBeTrue()
                    ->and($modifiedFile->isAdded())->toBeFalse()
                    ->and($addedFile->isAdded())->toBeTrue()
                    ->and($addedFile->isModified())->toBeFalse()
                    ->and($deletedFile->isDeleted())->toBeTrue()
                    ->and($renamedFile->isRenamed())->toBeTrue();
            });
        });

        describe('File Analysis Methods', function () {
            it('extracts file information correctly', function () {
                $dto = PullRequestFileDTO::fromApiResponse($this->mockFileData);

                expect($dto->getExtension())->toBe('php')
                    ->and($dto->getDirectory())->toBe('src/Controllers')
                    ->and($dto->getBasename())->toBe('UserController')
                    ->and($dto->getFileType())->toBe('php');
            });

            it('detects test files correctly', function () {
                $testFile = PullRequestFileDTO::fromApiResponse($this->mockTestFileData);
                $regularFile = PullRequestFileDTO::fromApiResponse($this->mockFileData);

                expect($testFile->isTestFile())->toBeTrue()
                    ->and($regularFile->isTestFile())->toBeFalse();
            });

            it('detects config files correctly', function () {
                $configFile = PullRequestFileDTO::fromApiResponse($this->mockConfigFileData);
                $regularFile = PullRequestFileDTO::fromApiResponse($this->mockFileData);

                expect($configFile->isConfigFile())->toBeTrue()
                    ->and($regularFile->isConfigFile())->toBeFalse();
            });

            it('detects documentation files correctly', function () {
                $docFileData = array_merge($this->mockFileData, ['filename' => 'README.md']);
                $docFile = PullRequestFileDTO::fromApiResponse($docFileData);
                $regularFile = PullRequestFileDTO::fromApiResponse($this->mockFileData);

                expect($docFile->isDocumentationFile())->toBeTrue()
                    ->and($regularFile->isDocumentationFile())->toBeFalse();
            });
        });

        describe('Change Analysis Methods', function () {
            it('calculates change ratios correctly', function () {
                $dto = PullRequestFileDTO::fromApiResponse($this->mockFileData);

                expect($dto->getAdditionRatio())->toBeGreaterThan(0.7) // 45/57 ≈ 0.79
                    ->and($dto->getDeletionRatio())->toBeLessThan(0.3); // 12/57 ≈ 0.21
            });

            it('identifies large changes correctly', function () {
                $dto = PullRequestFileDTO::fromApiResponse($this->mockFileData);
                $largeChangeData = array_merge($this->mockFileData, ['changes' => 150]);
                $largeDto = PullRequestFileDTO::fromApiResponse($largeChangeData);

                expect($dto->isLargeChange())->toBeFalse() // 57 < 100
                    ->and($largeDto->isLargeChange())->toBeTrue(); // 150 >= 100
            });

            it('identifies addition/deletion only changes', function () {
                $additionOnlyData = array_merge($this->mockFileData, ['deletions' => 0]);
                $additionOnlyDto = PullRequestFileDTO::fromApiResponse($additionOnlyData);

                $deletionOnlyData = array_merge($this->mockFileData, ['additions' => 0]);
                $deletionOnlyDto = PullRequestFileDTO::fromApiResponse($deletionOnlyData);

                expect($additionOnlyDto->hasOnlyAdditions())->toBeTrue()
                    ->and($additionOnlyDto->hasOnlyDeletions())->toBeFalse()
                    ->and($deletionOnlyDto->hasOnlyDeletions())->toBeTrue()
                    ->and($deletionOnlyDto->hasOnlyAdditions())->toBeFalse();
            });
        });

        describe('Analysis Tags', function () {
            it('generates correct analysis tags', function () {
                $testFile = PullRequestFileDTO::fromApiResponse($this->mockTestFileData);
                $configFile = PullRequestFileDTO::fromApiResponse($this->mockConfigFileData);

                $testTags = $testFile->getAnalysisTags();
                $configTags = $configFile->getAnalysisTags();

                expect($testTags)->toContain('test')
                    ->and($testTags)->toContain('php')
                    ->and($testTags)->toContain('added')
                    ->and($testTags)->toContain('only-additions')
                    ->and($testTags)->toContain('large-change') // 120 changes
                    ->and($configTags)->toContain('config')
                    ->and($configTags)->toContain('unknown') // .env extension
                    ->and($configTags)->toContain('modified');
            });
        });

        describe('Summary Generation', function () {
            it('creates helpful summary for display', function () {
                $dto = PullRequestFileDTO::fromApiResponse($this->mockFileData);
                $summary = $dto->getSummary();

                expect($summary)->toHaveKey('file', 'src/Controllers/UserController.php')
                    ->and($summary)->toHaveKey('status', 'modified')
                    ->and($summary)->toHaveKey('changes', '+45/-12')
                    ->and($summary)->toHaveKey('type', 'php')
                    ->and($summary)->toHaveKey('size', 'normal');
            });
        });
    });

    describe('File Type Detection', function () {
        it('detects common programming languages', function () {
            $testCases = [
                ['file.php', 'php'],
                ['script.js', 'javascript'],
                ['component.tsx', 'react-typescript'],
                ['app.py', 'python'],
                ['Main.java', 'java'],
                ['main.go', 'go'],
                ['lib.rs', 'rust'],
                ['program.c', 'c'],
                ['program.cpp', 'cpp'],
            ];

            foreach ($testCases as [$filename, $expectedType]) {
                $fileData = array_merge($this->mockFileData, ['filename' => $filename]);
                $dto = PullRequestFileDTO::fromApiResponse($fileData);

                expect($dto->getFileType())->toBe($expectedType, "Failed for file: {$filename}");
            }
        });

        it('detects web technologies', function () {
            $testCases = [
                ['index.html', 'html'],
                ['styles.css', 'css'],
                ['styles.scss', 'sass'],
                ['Component.vue', 'vue'],
                ['Component.jsx', 'react'],
            ];

            foreach ($testCases as [$filename, $expectedType]) {
                $fileData = array_merge($this->mockFileData, ['filename' => $filename]);
                $dto = PullRequestFileDTO::fromApiResponse($fileData);

                expect($dto->getFileType())->toBe($expectedType, "Failed for file: {$filename}");
            }
        });

        it('detects data formats and configs', function () {
            $testCases = [
                ['data.json', 'json'],
                ['config.yaml', 'yaml'],
                ['settings.toml', 'toml'],
                ['database.sql', 'sql'],
                ['README.md', 'markdown'],
                ['.env', 'environment'],
                ['Dockerfile', 'docker'],
            ];

            foreach ($testCases as [$filename, $expectedType]) {
                $fileData = array_merge($this->mockFileData, ['filename' => $filename]);
                $dto = PullRequestFileDTO::fromApiResponse($fileData);

                expect($dto->getFileType())->toBe($expectedType, "Failed for file: {$filename}");
            }
        });

        it('returns unknown for unrecognized file types', function () {
            $fileData = array_merge($this->mockFileData, ['filename' => 'binary.exe']);
            $dto = PullRequestFileDTO::fromApiResponse($fileData);

            expect($dto->getFileType())->toBe('unknown');
        });
    });

    describe('Edge Cases', function () {
        it('handles files with no changes', function () {
            $noChangeData = array_merge($this->mockFileData, [
                'additions' => 0,
                'deletions' => 0,
                'changes' => 0,
            ]);
            $dto = PullRequestFileDTO::fromApiResponse($noChangeData);

            expect($dto->getAdditionRatio())->toBe(0.0)
                ->and($dto->getDeletionRatio())->toBe(0.0)
                ->and($dto->hasOnlyAdditions())->toBeFalse()
                ->and($dto->hasOnlyDeletions())->toBeFalse();
        });

        it('handles optional fields correctly', function () {
            $minimalData = [
                'sha' => 'abc123',
                'filename' => 'test.php',
                'status' => 'added',
                'additions' => 10,
                'deletions' => 0,
                'changes' => 10,
                'blob_url' => 'https://example.com/blob',
                'raw_url' => 'https://example.com/raw',
                'contents_url' => 'https://example.com/contents',
                // No patch or previous_filename
            ];

            $dto = PullRequestFileDTO::fromApiResponse($minimalData);

            expect($dto->patch)->toBeNull()
                ->and($dto->previous_filename)->toBeNull()
                ->and($dto->filename)->toBe('test.php');
        });

        it('handles files in root directory', function () {
            $rootFileData = array_merge($this->mockFileData, ['filename' => 'composer.json']);
            $dto = PullRequestFileDTO::fromApiResponse($rootFileData);

            expect($dto->getDirectory())->toBe('.')
                ->and($dto->getBasename())->toBe('composer')
                ->and($dto->getExtension())->toBe('json');
        });
    });
});
