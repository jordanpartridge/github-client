<?php

namespace JordanPartridge\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \JordanPartridge\GithubClient\Resources\RepoResource repos()
 * @method static \JordanPartridge\GithubClient\Resources\CommitResource commits()
 * @method static \JordanPartridge\GithubClient\Resources\FileResource files()
 * @method static \JordanPartridge\GithubClient\Resources\PullRequestResource pullRequests()
 * @method static \JordanPartridge\GithubClient\Resources\ActionsResource actions()
 * @method static \JordanPartridge\GithubClient\Resources\IssuesResource issues()
 * @method static \JordanPartridge\GithubClient\Resources\CommentsResource comments()
 * @method static \JordanPartridge\GithubClient\Resources\ReleasesResource releases()
 * @method static \JordanPartridge\GithubClient\Resources\InstallationsResource installations()
 * @method static \JordanPartridge\GithubClient\Connectors\GithubConnector connector()
 * @method static array getRateLimitStatus()
 * @method static \JordanPartridge\GithubClient\Data\RateLimitDTO getRateLimitForResource(string $resource = 'core')
 * @method static bool hasRateLimitExceeded()
 * @method static \JordanPartridge\GithubClient\Data\Repos\RepoData getRepo(string $fullName)
 * @method static \Saloon\Http\Response deleteRepo(string $fullName)
 * @method static self forInstallation(int $installationId)
 * @method static self withApp(string $appId, string $privateKey, ?int $installationId = null)
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
