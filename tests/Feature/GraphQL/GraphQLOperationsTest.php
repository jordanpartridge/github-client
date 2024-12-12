<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use JordanPartridge\GithubClient\Connectors\GraphQLConnector;

class GraphQLOperationsTest extends TestCase
{
    /** @test */
    public function it_can_execute_graphql_query()
    {
        $connector = new GraphQLConnector('test-token');
        $query = <<<'GRAPHQL'
        query {
            viewer {
                login
            }
        }
        GRAPHQL;

        $response = $connector->executeQuery($query);
        $this->assertTrue($response->successful());
    }
}
