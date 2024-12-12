<?php

namespace Tests\Unit\DTO;

use Tests\TestCase;
use JordanPartridge\GithubClient\DTO\File;

class FileTest extends TestCase
{
    /** @test */
    public function it_can_create_File_from_array()
    {
        $data = [
            // TODO: Add test data
        ];

        $dto = File::from($data);
        
        $this->assertEquals('test', $dto->sha);
        $this->assertEquals('test', $dto->filename);
        $this->assertEquals('test', $dto->status);
        $this->assertEquals(1, $dto->additions);
        $this->assertEquals(1, $dto->deletions);
        $this->assertEquals(1, $dto->changes);
        $this->assertNull($dto->patch);
    }
}