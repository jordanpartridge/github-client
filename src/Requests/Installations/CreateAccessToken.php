<?php

namespace JordanPartridge\GithubClient\Requests\Installations;

use JordanPartridge\GithubClient\Data\Installations\InstallationTokenData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateAccessToken extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  int  $installationId  The installation ID
     * @param  array|null  $repositories  Optional array of repository names to limit access
     * @param  array|null  $permissions  Optional permissions to request
     */
    public function __construct(
        protected int $installationId,
        protected ?array $repositories = null,
        protected ?array $permissions = null,
    ) {}

    protected function defaultBody(): array
    {
        return array_filter([
            'repositories' => $this->repositories,
            'permissions' => $this->permissions,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): InstallationTokenData
    {
        return InstallationTokenData::fromArray($response->json());
    }

    public function resolveEndpoint(): string
    {
        return "/app/installations/{$this->installationId}/access_tokens";
    }
}
