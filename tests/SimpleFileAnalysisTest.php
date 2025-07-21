<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestFileDTO;

describe('Simple File Analysis (Laravel 10 Compatible)', function () {

    it('can create and analyze file DTOs without external dependencies', function () {
        $fileData = [
            'sha' => 'abc123',
            'filename' => 'src/Test.php',
            'status' => 'modified',
            'additions' => 10,
            'deletions' => 5,
            'changes' => 15,
            'blob_url' => 'https://example.com/blob',
            'raw_url' => 'https://example.com/raw',
            'contents_url' => 'https://example.com/contents',
        ];

        $dto = PullRequestFileDTO::fromApiResponse($fileData);

        expect($dto->filename)->toBe('src/Test.php')
            ->and($dto->status)->toBe('modified')
            ->and($dto->additions)->toBe(10)
            ->and($dto->deletions)->toBe(5)
            ->and($dto->changes)->toBe(15)
            ->and($dto->getFileType())->toBe('php')
            ->and($dto->isModified())->toBeTrue()
            ->and($dto->isAdded())->toBeFalse();
    });

    it('correctly categorizes different file types', function () {
        $testCases = [
            ['test.php', 'php'],
            ['app.js', 'javascript'],
            ['component.vue', 'vue'],
            ['README.md', 'markdown'],
            ['config.yaml', 'yaml'],
        ];

        foreach ($testCases as [$filename, $expectedType]) {
            $fileData = [
                'sha' => 'test',
                'filename' => $filename,
                'status' => 'added',
                'additions' => 1,
                'deletions' => 0,
                'changes' => 1,
                'blob_url' => 'https://example.com/blob',
                'raw_url' => 'https://example.com/raw',
                'contents_url' => 'https://example.com/contents',
            ];

            $dto = PullRequestFileDTO::fromApiResponse($fileData);
            expect($dto->getFileType())->toBe($expectedType);
        }
    });

    it('detects special file types correctly', function () {
        $fileData = [
            'sha' => 'test',
            'filename' => 'tests/ExampleTest.php',
            'status' => 'added',
            'additions' => 50,
            'deletions' => 0,
            'changes' => 50,
            'blob_url' => 'https://example.com/blob',
            'raw_url' => 'https://example.com/raw',
            'contents_url' => 'https://example.com/contents',
        ];

        $dto = PullRequestFileDTO::fromApiResponse($fileData);

        expect($dto->isTestFile())->toBeTrue()
            ->and($dto->getFileType())->toBe('php')
            ->and($dto->isAdded())->toBeTrue()
            ->and($dto->hasOnlyAdditions())->toBeTrue();
    });
});
