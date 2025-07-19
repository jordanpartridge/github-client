<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\Tests\TestCase;

describe('PullRequestDTO defensive programming', function () {
    it('handles missing optional fields gracefully', function () {
        // Create a complete mock first, then remove optional fields
        $completeData = $this->createMockPullRequestData();
        
        // Remove optional fields that might be missing from some GitHub API responses
        unset($completeData['comments']);
        unset($completeData['review_comments']);
        unset($completeData['commits']);
        unset($completeData['additions']);
        unset($completeData['deletions']);
        unset($completeData['changed_files']);
        unset($completeData['merged_at']);
        unset($completeData['closed_at']);

        $dto = PullRequestDTO::fromApiResponse($completeData);

        expect($dto->comments)->toBe(0)
            ->and($dto->review_comments)->toBe(0)
            ->and($dto->commits)->toBe(0)
            ->and($dto->additions)->toBe(0)
            ->and($dto->deletions)->toBe(0)
            ->and($dto->changed_files)->toBe(0)
            ->and($dto->merged_at)->toBeNull()
            ->and($dto->closed_at)->toBeNull()
            ->and($dto->title)->toBe('Test Pull Request')
            ->and($dto->number)->toBe(1);
    });

    it('handles all optional fields when present', function () {
        $completeData = $this->createMockPullRequestData([
            'comments' => 5,
            'review_comments' => 3,
            'commits' => 2,
            'additions' => 100,
            'deletions' => 50,
            'changed_files' => 4,
            'merged_at' => '2024-01-02T00:00:00Z',
            'closed_at' => '2024-01-02T00:00:00Z',
        ]);

        $dto = PullRequestDTO::fromApiResponse($completeData);

        expect($dto->comments)->toBe(5)
            ->and($dto->review_comments)->toBe(3)
            ->and($dto->commits)->toBe(2)
            ->and($dto->additions)->toBe(100)
            ->and($dto->deletions)->toBe(50)
            ->and($dto->changed_files)->toBe(4)
            ->and($dto->merged_at)->toBe('2024-01-02T00:00:00Z')
            ->and($dto->closed_at)->toBe('2024-01-02T00:00:00Z');
    });
});