<?php

namespace JordanPartridge\GithubClient\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class GraphQLRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected ?string $method = 'POST';

    /**
     * The GraphQL query
     */
    protected string $query;

    /**
     * The variables for the query
     */
    protected array $variables;

    /**
     * Constructor
     */
    public function __construct(string $query = '', array $variables = [])
    {
        $this->query = $query;
        $this->variables = $variables;
    }

    /**
     * Define the endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '';
    }

    /**
     * Define the default body for the request
     */
    protected function defaultBody(): array
    {
        return [
            'query' => $this->query,
            'variables' => $this->variables
        ];
    }
}