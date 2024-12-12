<?php

namespace JordanPartridge\GithubClient\Connectors;

use JordanPartridge\GithubClient\Contracts\AbstractGithubConnector;
use JordanPartridge\GithubClient\Requests\GraphQLRequest;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;
use Saloon\Contracts\ArrayStore;

class GraphQLConnector extends AbstractGithubConnector
{
    /**
     * Resolve the base URL for GitHub GraphQL API.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.github.com/graphql';
    }

    /**
     * Override default headers to include GraphQL specifics.
     */
    protected function defaultHeaders(): array
    {
        $headers = parent::defaultHeaders();
        $headers['Content-Type'] = 'application/json';
        return $headers;
    }

    /**
     * Define the default query request.
     */
    protected function defaultQuery(): GraphQLRequest
    {
        return new GraphQLRequest();
    }

    /**
     * Execute a GraphQL query.
     *
     * @param string $query
     * @param array $variables
     * @return ArrayStore
     *
     * @throws RequestException
     */
    public function query(string $query, array $variables = []): ArrayStore
    {
        $request = new GraphQLRequest($query, $variables);
        return $this->send($request);
    }

    /**
     * Get repositories for a user using GraphQL.
     *
     * @param string $owner
     * @param array $fields
     * @return Response
     * @throws RequestException
     */
    public function repos(string $owner, array $fields = []): Response
    {
        $defaultFields = [
            'name',
            'description',
            'url'
        ];

        $fields = $fields ?: $defaultFields;
        $fieldsString = implode("\n", $fields);

        $query = <<<GRAPHQL
        query (\$owner: String!) {
            user(login: \$owner) {
                repositories(first: 100) {
                    nodes {
                        {$fieldsString}
                    }
                }
            }
        }
        GRAPHQL;

        return $this->query($query, ['owner' => $owner]);
    }
}
