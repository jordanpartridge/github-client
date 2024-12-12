<?php

namespace Tests\Unit\DTO;

use JordanPartridge\GithubClient\DTO\Repositories\Repository;
use JordanPartridge\GithubClient\DTO\Users\User;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    /** @test */
    public function it_can_create_repository_from_array()
    {
        $data = [
            'id' => 1296269,
            'name' => 'Hello-World',
            'full_name' => 'octocat/Hello-World',
            'owner' => [
                'id' => 1,
                'login' => 'octocat',
                'avatar_url' => 'https://github.com/images/error/octocat_happy.gif',
                'url' => 'https://api.github.com/users/octocat',
                'html_url' => 'https://github.com/octocat',
                'type' => 'User',
                'site_admin' => false,
            ],
            'private' => false,
            'description' => 'This your first repo!',
            'fork' => false,
            'language' => 'JavaScript',
            'default_branch' => 'main',
            'topics' => ['octocat', 'atom', 'electron', 'api'],
            'stargazers_count' => 80,
            'watchers_count' => 80,
            'forks_count' => 9,
            'created_at' => '2011-01-26T19:01:12Z',
            'updated_at' => '2011-01-26T19:14:43Z',
            'pushed_at' => '2011-01-26T19:06:43Z',
        ];

        $repository = Repository::from($data);

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertEquals(1296269, $repository->id);
        $this->assertEquals('Hello-World', $repository->name);
        $this->assertEquals('octocat/Hello-World', $repository->full_name);
        $this->assertInstanceOf(User::class, $repository->owner);
        $this->assertFalse($repository->private);
        $this->assertEquals('This your first repo!', $repository->description);
        $this->assertFalse($repository->fork);
        $this->assertEquals('JavaScript', $repository->language);
        $this->assertEquals('main', $repository->default_branch);
        $this->assertEquals(['octocat', 'atom', 'electron', 'api'], $repository->topics);
        $this->assertEquals(80, $repository->stargazers_count);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Spatie\LaravelData\Exceptions\InvalidDataException::class);

        Repository::from([
            'name' => 'Hello-World',
            // Missing required fields
        ]);
    }
}