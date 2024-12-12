<?php

namespace Tests\Unit\DTO;

use Tests\TestCase;
use JordanPartridge\GithubClient\DTO\Issue;

class IssueTest extends TestCase
{
    /** @test */
    public function it_can_create_Issue_from_array()
    {
        $data = [
            // TODO: Add test data
        ];

        $dto = Issue::from($data);
        
        $this->assertEquals(1, $dto->id);
        $this->assertNull($dto->node_id);
        $this->assertEquals(1, $dto->number);
        $this->assertEquals('test', $dto->title);
        $this->assertEquals(null, $dto->user);
        $this->assertEquals('test', $dto->state);
        $this->assertEquals(true, $dto->locked);
        $this->assertNull($dto->assignee);
        $this->assertEquals([], $dto->assignees);
        $this->assertEquals(1, $dto->comments);
        $this->assertEquals('test', $dto->created_at);
        $this->assertEquals('test', $dto->updated_at);
        $this->assertNull($dto->closed_at);
    }
}