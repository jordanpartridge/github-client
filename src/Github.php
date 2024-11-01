<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Requests\Repos\Repos;
use JordanPartridge\GithubClient\Resources\RepoResource;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class Github
{
    public function __construct(
        protected GithubConnectorInterface $connector,
    ) {}


    public function repos(): RepoResource
    {
        return new RepoResource($this->connector);
    }
}
