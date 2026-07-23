<?php

namespace JordanPartridge\GithubClient\Resources;

use InvalidArgumentException;
use JordanPartridge\GithubClient\Requests\Files\GetContents;
use JordanPartridge\GithubClient\Requests\Files\Index;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Response;

readonly class FileResource extends BaseResource
{
    public function all(string $repo_name, string $commit_sha): Response
    {
        $repo = Repo::fromFullName($repo_name);

        if (! preg_match('/^[0-9a-f]{40}$/i', $commit_sha)) {
            throw new InvalidArgumentException('Invalid commit SHA format');
        }

        return $this->github()->connector()->send(new Index($repo->fullName(), $commit_sha));
    }

    /**
     * Get the contents of a file at a specific ref (branch, tag, or SHA).
     *
     * Note: If the path is a directory, GitHub returns an array of entries instead.
     *
     * @return array{name: string, path: string, sha: string, size: int, content: string, encoding: string}|array<int, array<string, mixed>>
     */
    public function contents(string $owner, string $repo, string $path, ?string $ref = null): array
    {
        $response = $this->github()->connector()->send(
            new GetContents($owner, $repo, $path, $ref),
        );

        return $response->json();
    }

    /**
     * Get the decoded contents of a file at a specific ref.
     */
    public function getContent(string $owner, string $repo, string $path, ?string $ref = null): string
    {
        $data = $this->contents($owner, $repo, $path, $ref);

        if ($data['encoding'] === 'base64') {
            return base64_decode($data['content']);
        }

        return $data['content'];
    }
}
