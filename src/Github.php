<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Requests\Repos\Repos;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class Github
{
    public function __construct(
        protected GithubConnectorInterface $connector,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function repos(...$args): Response
    {
        return $this->connector->send(request: new Repos(...$args));
    }
}
