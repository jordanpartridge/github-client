<?php

namespace JordanPartridge\GithubClient\Requests\Installations;

use JordanPartridge\GithubClient\Data\Installations\InstallationData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetInstallation extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int $installationId,
    ) {}

    public function createDtoFromResponse(Response $response): InstallationData
    {
        return InstallationData::fromArray($response->json());
    }

    public function resolveEndpoint(): string
    {
        return "/app/installations/{$this->installationId}";
    }
}
