<?php

namespace JordanPartridge\GithubClient;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Connectors\GraphQLConnector;
use JordanPartridge\GithubClient\Connectors\RestConnector;

class Github
{
    protected RestConnector|GraphQLConnector $connector;

    protected ?string $token;

    public function __construct(?string $token = null, ?string $connector = null)
    {
        $this->token = $token ?? config('github-client.token');

        $this->connector = $this->determineConnector($connector);
    }

    public function repos()
    {
        return $this->connector->repos();
    }

    public function pulls()
    {
        return $this->connector->pulls();
    }

    /**
     * Select a connector by type.
     *
     * @throws InvalidArgumentException
     */
    public function connector(?string $type = 'rest'): RestConnector|GraphQLConnector
    {
        return match (strtolower($type)) {
            'graphql', 'graph' => new GraphQLConnector($this->token),
            'rest' => new RestConnector($this->token),
            default => throw new InvalidArgumentException("Invalid connector type: $type")
        };
    }

    private function determineConnector(?string $connector = null): GraphQLConnector|RestConnector
    {
        if ($connector) {
            return $this->connector($connector);
        }

        return $this->connector(config('github-client.connector'));
    }
}
