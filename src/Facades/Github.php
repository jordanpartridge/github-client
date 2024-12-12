<?php

namespace JordanPartridge\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \JordanPartridge\GithubClient\Connectors\RestConnector rest()
 * @method static \JordanPartridge\GithubClient\Connectors\GraphQLConnector graphql()
 * @method static \JordanPartridge\GithubClient\Connectors\RestConnector|\JordanPartridge\GithubClient\Connectors\GraphQLConnector connector(string $type = 'rest')
 *
 * @see \JordanPartridge\GithubClient\Github
 */
class Github extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \JordanPartridge\GithubClient\Github::class;
    }
}
