<?php

use JordanPartridge\GithubClient\Data\Pulls\PullRequestDetailDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTOFactory;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestSummaryDTO;

describe('DTO Pattern: Summary vs Detail DTOs', function () {
    beforeEach(function () {
        $this->listResponseData = [
            'id' => 12345,
            'number' => 47,
            'state' => 'open',
            'title' => '🚀 Phase 1 GitHub Integration',
            'body' => 'PR description...',
            'html_url' => 'https://github.com/test/repo/pull/47',
            'diff_url' => 'https://github.com/test/repo/pull/47.diff',
            'patch_url' => 'https://github.com/test/repo/pull/47.patch',
            'base' => ['ref' => 'main'],
            'head' => ['ref' => 'feature'],
            'draft' => false,
            'merged' => false,
            'merged_at' => null,
            'merge_commit_sha' => null,
            'user' => $this->createMockUserData('author'),
            'merged_by' => null,
            'created_at' => '2023-01-01T12:00:00Z',
            'updated_at' => '2023-01-01T13:00:00Z',
            'closed_at' => null,
            // Note: NO detailed fields like comments, additions, etc.
        ];

        $this->detailResponseData = array_merge($this->listResponseData, [
            // Additional detailed fields only in individual endpoint
            'comments' => 1,
            'review_comments' => 19,
            'commits' => 8,
            'additions' => 456,
            'deletions' => 23,
            'changed_files' => 15,
        ]);
    });

    describe('PullRequestSummaryDTO', function () {
        it('creates from list endpoint data correctly', function () {
            $dto = PullRequestSummaryDTO::fromListResponse($this->listResponseData);

            expect($dto)->toBeInstanceOf(PullRequestSummaryDTO::class)
                ->and($dto->number)->toBe(47)
                ->and($dto->title)->toContain('Phase 1')
                ->and($dto->hasDetailedData())->toBeFalse();
        });

        it('converts to array without detailed fields', function () {
            $dto = PullRequestSummaryDTO::fromListResponse($this->listResponseData);
            $array = $dto->toArray();

            expect($array)->toHaveKey('number', 47)
                ->and($array)->toHaveKey('title')
                ->and($array)->not->toHaveKey('comments')
                ->and($array)->not->toHaveKey('additions');
        });
    });

    describe('PullRequestDetailDTO', function () {
        it('creates from detail endpoint data correctly', function () {
            $dto = PullRequestDetailDTO::fromDetailResponse($this->detailResponseData);

            expect($dto)->toBeInstanceOf(PullRequestDetailDTO::class)
                ->and($dto)->toBeInstanceOf(PullRequestSummaryDTO::class) // Inheritance
                ->and($dto->number)->toBe(47)
                ->and($dto->comments)->toBe(1)
                ->and($dto->review_comments)->toBe(19)
                ->and($dto->additions)->toBe(456)
                ->and($dto->deletions)->toBe(23)
                ->and($dto->hasDetailedData())->toBeTrue();
        });

        it('provides helpful utility methods', function () {
            $dto = PullRequestDetailDTO::fromDetailResponse($this->detailResponseData);

            expect($dto->getTotalLinesChanged())->toBe(479) // 456 + 23
                ->and($dto->getAdditionRatio())->toBeGreaterThan(0.9) // Mostly additions
                ->and($dto->hasComments())->toBeTrue()
                ->and($dto->getTotalComments())->toBe(20); // 1 + 19
        });

        it('creates helpful summary for display', function () {
            $dto = PullRequestDetailDTO::fromDetailResponse($this->detailResponseData);
            $summary = $dto->getSummary();

            expect($summary)->toHaveKey('pr', '#47: 🚀 Phase 1 GitHub Integration')
                ->and($summary['stats']['comments'])->toBe(1)
                ->and($summary['stats']['review_comments'])->toBe(19)
                ->and($summary['stats']['changes'])->toBe('+456/-23');
        });

        it('converts to array with detailed fields', function () {
            $dto = PullRequestDetailDTO::fromDetailResponse($this->detailResponseData);
            $array = $dto->toArray();

            expect($array)->toHaveKey('number', 47)
                ->and($array)->toHaveKey('comments', 1)
                ->and($array)->toHaveKey('review_comments', 19)
                ->and($array)->toHaveKey('additions', 456)
                ->and($array)->toHaveKey('deletions', 23);
        });
    });

    describe('PullRequestDTOFactory', function () {
        it('detects list response and creates summary DTO', function () {
            $dto = PullRequestDTOFactory::fromResponse($this->listResponseData);

            expect($dto)->toBeInstanceOf(PullRequestSummaryDTO::class)
                ->and($dto)->not->toBeInstanceOf(PullRequestDetailDTO::class);
        });

        it('detects detail response and creates detail DTO', function () {
            $dto = PullRequestDTOFactory::fromResponse($this->detailResponseData);

            expect($dto)->toBeInstanceOf(PullRequestDetailDTO::class)
                ->and($dto)->toBeInstanceOf(PullRequestSummaryDTO::class); // Inheritance
        });

        it('forces creation of specific DTO types', function () {
            // Force summary creation even with detail data
            $summaryDto = PullRequestDTOFactory::createSummary($this->detailResponseData);
            expect($summaryDto)->toBeInstanceOf(PullRequestSummaryDTO::class)
                ->and($summaryDto)->not->toBeInstanceOf(PullRequestDetailDTO::class);

            // Force detail creation
            $detailDto = PullRequestDTOFactory::createDetail($this->detailResponseData);
            expect($detailDto)->toBeInstanceOf(PullRequestDetailDTO::class);
        });

        it('handles array of responses', function () {
            $responses = [$this->listResponseData, $this->detailResponseData];
            $dtos = PullRequestDTOFactory::fromResponseArray($responses);

            expect($dtos)->toHaveCount(2)
                ->and($dtos[0])->toBeInstanceOf(PullRequestSummaryDTO::class)
                ->and($dtos[1])->toBeInstanceOf(PullRequestDetailDTO::class);
        });

        it('provides response analysis for debugging', function () {
            $listAnalysis = PullRequestDTOFactory::analyzeResponse($this->listResponseData);
            $detailAnalysis = PullRequestDTOFactory::analyzeResponse($this->detailResponseData);

            expect($listAnalysis['would_create'])->toBe('PullRequestSummaryDTO')
                ->and($listAnalysis['has_detailed_fields'])->toBeFalse()
                ->and($detailAnalysis['would_create'])->toBe('PullRequestDetailDTO')
                ->and($detailAnalysis['has_detailed_fields'])->toBeTrue();
        });
    });

    describe('Type Safety Benefits', function () {
        it('prevents misleading zero values in summary DTOs', function () {
            $summaryDto = PullRequestDTOFactory::fromResponse($this->listResponseData);

            // Summary DTO doesn't have these fields - compile-time safety!
            expect($summaryDto)->toBeInstanceOf(PullRequestSummaryDTO::class);
            
            // This would be a compile error in strict mode:
            // $summaryDto->comments // Property doesn't exist!
        });

        it('guarantees accurate values in detail DTOs', function () {
            $detailDto = PullRequestDTOFactory::fromResponse($this->detailResponseData);

            expect($detailDto)->toBeInstanceOf(PullRequestDetailDTO::class)
                ->and($detailDto->comments)->toBe(1) // Guaranteed accurate
                ->and($detailDto->review_comments)->toBe(19); // Guaranteed accurate
        });
    });

    describe('Backward Compatibility', function () {
        it('maintains compatibility with existing PullRequestDTO usage', function () {
            // The original PullRequestDTO still works exactly as before
            $originalDto = \JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO::fromApiResponse($this->detailResponseData);

            expect($originalDto->number)->toBe(47)
                ->and($originalDto->comments)->toBe(1)
                ->and($originalDto->review_comments)->toBe(19);
        });
    });
});