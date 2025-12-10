<?php

namespace JordanPartridge\GithubClient\Requests\Installations;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Data\Installations\InstallationData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListInstallations extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  int|null  $per_page  Number of results per page (max 100)
     * @param  int|null  $page  Page number of the results to fetch
     */
    public function __construct(
        protected ?int $per_page = null,
        protected ?int $page = null,
    ) {
        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
            throw new InvalidArgumentException('Per page must be between 1 and 100');
        }
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'per_page' => $this->per_page,
            'page' => $this->page,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return array_map(
            fn ($installation) => InstallationData::fromArray($installation),
            $response->json(),
        );
    }

    public function resolveEndpoint(): string
    {
        return '/app/installations';
    }
}
