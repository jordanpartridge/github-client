<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Requests\Repos\Repos;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class Github
{
    public function __construct(
        protected GithubConnector $connector,
    )
    {
    }

    /**
     * @param ...$args
     * @throws FatalRequestException
     * @throws RequestException
     * @return Response
     */
    public function repos(...$args): Response
    {
        return $this->connector->send(request: new Repos(...$args));
    }
}
