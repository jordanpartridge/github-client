<?php

namespace JordanPartridge\GithubClient\Requests\Files;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class Index extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private $repo_name, private $commit_sha)
    {
    }

    public function resolveEndpoint(): string
    {
        return 'repos/' . $this->repo_name . '/commits/' . $this->commit_sha . '/files';
    }
}
