<?php

namespace Tests\Unit\DTO;

use JordanPartridge\GithubClient\DTO\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function it_can_create_user_from_array()
    {
        $data = [
            // TODO: Add test data
        ];

        $dto = User::from($data);

        $this->assertEquals(1, $dto->id);
        $this->assertEquals('test', $dto->login);
        $this->assertNull($dto->node_id);
        $this->assertEquals('test', $dto->avatar_url);
        $this->assertEquals('test', $dto->url);
        $this->assertEquals('test', $dto->type);
        $this->assertEquals(true, $dto->site_admin);
    }
}
