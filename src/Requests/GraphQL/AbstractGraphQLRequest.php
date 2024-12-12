<?php

namespace JordanPartridge\GithubClient\Requests\GraphQL;

use Saloon\Contracts\Body\HasBody;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

abstract class AbstractGraphQLRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected ?string $method = 'POST';

    /**
     * The variables for the query
     */
    protected array $variables = [];

    /**
     * Constructor
     */
    public function __construct(array $variables = [])
    {
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
     * Get the GraphQL operation type (query/mutation)
     */
    abstract protected function getOperation(): string;

    /**
     * Get the default fields to request
     *
     * @return array
     */
    abstract protected function getDefaultFields(): array;

    /**
     * Define the default body for the request
     */
    protected function defaultBody(): array
    {
        return [
            'query' => $this->buildQuery(),
            'variables' => $this->variables
        ];
    }

    /**
     * Build the GraphQL query string
     */
    protected function buildQuery(): string
    {
        // To be implemented by child classes
        return '';
    }
}