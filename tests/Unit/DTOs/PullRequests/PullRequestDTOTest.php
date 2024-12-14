<?php

namespace Tests\Unit\DTOs\PullRequests;

use Tests\TestCase;

class PullRequestDTOTest extends TestCase
{
    /** @test */
    public function it_can_create_pull_request_dto_from_array()
    {
        $data = [
            'id' => 1,
            'number' => 123,
            'title' => 'Test PR',
            'body' => 'Test PR description',
            'state' => 'open',
        ];

        $this->markTestIncomplete('Implementation pending');
    }
}
