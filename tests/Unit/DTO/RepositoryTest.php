<?php

namespace Tests\Unit\DTO;

use Tests\TestCase;
use JordanPartridge\GithubClient\DTO\Repository;

class RepositoryTest extends TestCase
{
    /** @test */
    public function it_can_create_Repository_from_array()
    {
        $data = [
            // TODO: Add test data
        ];

        $dto = Repository::from($data);
        
        $this->assertEquals(1, $dto->id);
        $this->assertNull($dto->node_id);
        $this->assertEquals('test', $dto->name);
        $this->assertEquals('test', $dto->full_name);
        $this->assertEquals(null, $dto->owner);
        $this->assertEquals(true, $dto->private);
        $this->assertNull($dto->description);
        $this->assertEquals(true, $dto->fork);
        $this->assertNull($dto->language);
        $this->assertEquals('test', $dto->default_branch);
        $this->assertNull($dto->topics);
    }
}