<?php

namespace JordanPartridge\GithubClient\Requests\Repos;

use InvalidArgumentException;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class Repos extends Request
{
    /**
     * GitHub User Repositories Request
     *
     * This endpoint works with the following fine-grained token types:
     *
     * GitHub App user access tokens
     * Fine-grained personal access tokens
     * The fine-grained token must have the following permission set:
     *
     * "Metadata" repository permissions (read)
     * This endpoint can be used without authentication or the aforementioned permissions
     * if only public resources are requested.
     *
     * Fetches repositories for the authenticated user.
     *
     * @see https://docs.github.com/en/rest/repos/repos#list-repositories-for-the-authenticated-user
     */
    protected Method $method = Method::GET;

    /**
     * @param  int|null  $page  Page number
     * @param  string|null  $visibility  Can be one of: all, public, private
     * @param  string|null  $sort  Can be one of: created, updated, pushed, full_name
     * @param  string|null  $direction  Can be one of: asc, desc
     */
    public function __construct(
        protected ?string $per_page = null,
        protected ?int $page = null,
        protected ?string $visibility = null,
        protected ?string $sort = null,
        protected ?string $direction = null,
    ) {
        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
                      throw new InvalidArgumentException('Per page must be between 1 and 100');
        }
        if ($this->visibility !== null && ! in_array($this->visibility, ['all', 'public', 'private'])) {
            throw new InvalidArgumentException('Visibility must be one of: all, public, private');
        }

        if ($this->sort !== null && ! in_array($this->sort, ['created', 'updated', 'pushed', 'full_name'])) {
            throw new InvalidArgumentException('Sort must be one of: created, updated, pushed, full_name');
        }

        if ($this->direction !== null && ! in_array($this->direction, ['asc', 'desc'])) {
            throw new InvalidArgumentException('Direction must be one of: asc, desc');
        }
    }

    protected function defaultQuery(): array
    {
        return [
            'per_page' => $this->per_page,
            'page' => $this->page,
            'visibility' => $this->visibility,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ];
    }

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/user/repos';
    }
}
