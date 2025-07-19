<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestFileDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class Files extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $repo,
        protected int $number,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->repo}/pulls/{$this->number}/files";
    }

    public function createDtoFromResponse(Response $response): array
    {
        $data = $response->json();

        return array_map(
            fn(array $file) => PullRequestFileDTO::fromApiResponse($file),
            $data
        );
    }
}