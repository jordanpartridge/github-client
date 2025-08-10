<?php

namespace JordanPartridge\GithubClient\Requests\Releases;

use JordanPartridge\GithubClient\Data\Releases\ReleaseData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Get extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int  $releaseId  The unique identifier of the release
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $releaseId,
    ) {}

    public function createDtoFromResponse(Response $response): ReleaseData
    {
        return ReleaseData::fromArray($response->json());
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/releases/{$this->releaseId}";
    }
}
