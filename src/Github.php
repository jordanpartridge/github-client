
<?php

namespace JordanPartridge\GithubClient;

use JordanPartridge\GithubClient\Contracts\GithubConnectorInterface;
use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Resources\CommitResource;
use JordanPartridge\GithubClient\Resources\FileResource;
use JordanPartridge\GithubClient\Resources\RepoResource;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Response;

class Github
{
    use Concerns\ValidatesRepoName;

    public function __construct(
        protected GithubConnectorInterface $connector,
    ) {}

    public function connector(): GithubConnectorInterface
    {
        return $this->connector;
    }

    public function repos(): RepoResource
    {
        return $this->connector->repos();
    }

    public function commits(): CommitResource
    {
        return $this->connector->commits();
    }

    public function files(): FileResource
    {
        return $this->connector->files();
    }

    /**
     * Get a repository by its full name
     *
     * @param  string  $fullName  Repository full name (e.g. "owner/repo")
     */
    public function getRepo(string $fullName): RepoData
    {
        $repo = Repo::fromFullName($fullName);

        return $this->repos()->get($repo);
    }

    /**
     * Delete a repository by its full name
     *
     * @param  string  $fullName  Repository full name (e.g. "owner/repo")
     */
    public function deleteRepo(string $fullName): Response
    {
        $repo = Repo::fromFullName($fullName);

        return $this->repos()->delete($repo);
    }
}
