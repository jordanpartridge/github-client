<?php

namespace Tests\Feature\REST;

use JordanPartridge\GithubClient\Connectors\RestConnector;
use Tests\TestCase;

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
