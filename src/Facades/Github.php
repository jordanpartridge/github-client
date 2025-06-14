<?php

namespace JordanPartridge\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \JordanPartridge\GithubClient\Resources\RepoResource repos()
 * @method static \JordanPartridge\GithubClient\Resources\CommitResource commits()
 * @method static \JordanPartridge\GithubClient\Resources\FileResource files()
 * @method static \JordanPartridge\GithubClient\Resources\PullRequestResource pullRequests()
 * @method static \JordanPartridge\GithubClient\Resources\ActionsResource actions()
 * @method static \JordanPartridge\GithubClient\Contracts\GithubConnectorInterface connector()
 * 
 * @see \JordanPartridge\GithubClient\Github
 */
class Github extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JordanPartridge\GithubClient\Github::class;
    }
}
