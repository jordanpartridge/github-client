<?php

namespace Tests\Unit\DTOs\Users;

use Tests\TestCase;
use JordanPartridge\GithubClient\DataTransferObjects\Users\UserDTO;

class UserDTOTest extends TestCase
{
    /** @test */
    public function it_can_create_user_dto_from_array()
    {
        $data = [
            'id' => 1,
            'login' => 'test-user',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_url' => 'https://example.com/avatar.png',
        ];

        $this->markTestIncomplete('Implementation pending');
    }
}
