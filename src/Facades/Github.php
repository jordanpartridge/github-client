<?php

namespace ShadeTheory\GithubClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ShadeTheory\GithubClient\Resources\RepoResource repos()
 * @method static \ShadeTheory\GithubClient\Resources\CommitResource commits()
 * @method static \ShadeTheory\GithubClient\Resources\FileResource files()
 * @method static \ShadeTheory\GithubClient\Resources\PullRequestResource pullRequests()
 * @method static \ShadeTheory\GithubClient\Resources\ActionsResource actions()
 * @method static \ShadeTheory\GithubClient\Resources\IssuesResource issues()
 * @method static \ShadeTheory\GithubClient\Resources\CommentsResource comments()
 * @method static \ShadeTheory\GithubClient\Resources\ReleasesResource releases()
 * @method static \ShadeTheory\GithubClient\Resources\InstallationsResource installations()
 * @method static \ShadeTheory\GithubClient\Connectors\GithubConnector connector()
 * @method static array getRateLimitStatus()
 * @method static \ShadeTheory\GithubClient\Data\RateLimitDTO getRateLimitForResource(string $resource = 'core')
 * @method static bool hasRateLimitExceeded()
 * @method static \ShadeTheory\GithubClient\Data\Repos\RepoData getRepo(string $fullName)
 * @method static \Saloon\Http\Response deleteRepo(string $fullName)
 * @method static self forInstallation(int $installationId)
 * @method static self withApp(string $appId, string $privateKey, ?int $installationId = null)
 *
 * @see \ShadeTheory\GithubClient\Github
 */
class Github extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ShadeTheory\GithubClient\Github::class;
    }
}
