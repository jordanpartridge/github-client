<?php

namespace Tests\Unit\DTOs\Commits;

use Tests\TestCase;
use JordanPartridge\GithubClient\DataTransferObjects\Commits\CommitDTO;

class CommitDTOTest extends TestCase
{
    /** @test */
    public function it_can_create_commit_dto_from_array()
    {
        $data = [
            'sha' => 'abc123',
            'commit' => [
                'message' => 'Test commit',
                'author' => [
                    'name' => 'Test Author',
                    'email' => 'test@example.com',
                    'date' => '2024-12-11T00:00:00Z'
                ]
            ]
        ];

        $this->markTestIncomplete('Implementation pending');
    }
}
