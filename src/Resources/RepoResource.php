<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Requests\Repos\Repo;
use JordanPartridge\GithubClient\Requests\Repos\Repos;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class RepoResource extends BaseResource
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function all(...$args): Response
    {
        return $this->connector->send(new Repos(...$args));
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function get(string $repo_name): Response
    {
        return $this->connector->send(new Repo($repo_name));
    }
}
