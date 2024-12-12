<?php

namespace Tests\Unit\DTOs\Repositories;

use Tests\TestCase;
use JordanPartridge\GithubClient\DataTransferObjects\Repositories\RepositoryDTO;

class RepositoryDTOTest extends TestCase
{
    /** @test */
    public function it_can_create_repository_dto_from_array()
    {
        $data = [
            'id' => 1,
            'name' => 'test-repo',
            'full_name' => 'jordanpartridge/test-repo',
            'description' => 'Test repository',
            'private' => false,
        ];

        $dto = RepositoryDTO::fromArray($data);

        $this->assertEquals($data['id'], $dto->id);
        $this->assertEquals($data['name'], $dto->name);
        $this->assertEquals($data['full_name'], $dto->fullName);
        $this->assertEquals($data['description'], $dto->description);
        $this->assertEquals($data['private'], $dto->private);
    }
}
