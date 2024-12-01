<?php

namespace JordanPartridge\GithubClient\Requests\Commits;

use Illuminate\Support\Carbon;
use InvalidArgumentException;
use JordanPartridge\GithubClient\Concerns\ValidatesRepoName;
use JordanPartridge\GithubClient\Data\Commits\CommitAuthorData;
use JordanPartridge\GithubClient\Data\Commits\CommitData;
use JordanPartridge\GithubClient\Data\Commits\CommitDetailsData;
use JordanPartridge\GithubClient\Data\FileDTO;
use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\TreeData;
use JordanPartridge\GithubClient\Data\VerificationData;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Spatie\LaravelData\DataCollection;

class Get extends Request
{
    use ValidatesRepoName;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly Repo   $repo,
        private readonly string $commit_sha,
    )
    {
        $this->validateSHA($commit_sha);
    }

    public function resolveEndpoint(): string
    {
        return '/repos/' . $this->repo->fullName() . '/commits/' . $this->commit_sha;
    }

    private function validateSHA(string $commit_sha): void
    {
        if (!preg_match('/^[0-9a-f]{40}$/i', $commit_sha)) {
            throw new InvalidArgumentException('Invalid commit SHA format');
        }
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $data = $response->json();

        return new CommitData(
            sha: $data['sha'],
            node_id: $data['node_id'],
            commit: new CommitDetailsData(
                author: new CommitAuthorData(
                    name: $data['commit']['author']['name'],
                    email: $data['commit']['author']['email'],
                    date: Carbon::parse($data['commit']['author']['date'])
                ),
                committer: new CommitAuthorData(
                    name: $data['commit']['committer']['name'],
                    email: $data['commit']['committer']['email'],
                    date: Carbon::parse($data['commit']['committer']['date'])
                ),
                message: $data['commit']['message'],
                tree: new TreeData(...$data['commit']['tree']),
                url: $data['commit']['url'],
                comment_count: $data['commit']['comment_count'],
                verification: new VerificationData(...$data['commit']['verification']),
                files: isset($data['files']) ? new DataCollection(
                    FileDTO::class,
                    array_map(function ($file) {
                        return new FileDTO(
                            sha: $file['sha'],
                            filename: $file['filename'],
                            status: $file['status'],
                            additions: $file['additions'],
                            deletions: $file['deletions'],
                            changes: $file['changes'],
                            raw_url: $file['raw_url'],
                            contents_url: $file['contents_url'],
                            blob_url: $file['blob_url'],
                            patch: $file['patch'] ?? null,
                            size: $file['size'] ?? null
                        );
                    }, $data['files'])
                ) : null
            ),
            url: $data['url'],
            html_url: $data['html_url'],
            comments_url: $data['comments_url'],
            author: $data['author'] ? new GitUserData(...$data['author']) : null,
            committer: $data['committer'] ? new GitUserData(...$data['committer']) : null,
            parents: $data['parents']
        );
    }
}
