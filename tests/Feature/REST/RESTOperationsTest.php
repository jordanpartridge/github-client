<?php

namespace Tests\Feature\REST;

use Tests\TestCase;
use JordanPartridge\GithubClient\Connectors\RestConnector;

class RESTOperationsTest extends TestCase
{
    /** @test */
    public function it_can_execute_rest_request()
    {
        $connector = new RestConnector('test-token');
        $response = $connector->get('user');
        $this->assertTrue($response->successful());
    }
}
