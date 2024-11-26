<?php

namespace JordanPartridge\GithubClient\Data;


readonly class CommitDTO
{

    public function __construct(
        private string          $sha,
        private CommitAuthorDTO $git_author,
        private CommitAuthorDTO $committer,
        private array           $files,
    )
    {
    }

    /**
     * Create a RepoDTO instance from an array of repository data
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(sha: $data['sha'],
            git_author: new  CommitAuthorDTO(
                name: $data['commit']['author']['name'],
                email: $data['commit']['author']['email'],
                date: $data['commit']['author']['date']
            ),
            committer: new  CommitAuthorDTO(
                name: $data['commit']['committer']['name'],
                email: $data['commit']['committer']['email'],
                date: $data['commit']['committer']['date']
            ), files: $data['files']
        );
    }

    public function getSha(): string
    {
        return $this->sha;
    }

    public function getGitAuthor(): CommitAuthorDTO
    {
        return $this->git_author;
    }

    public function getCommitter(): CommitAuthorDTO
    {
        return $this->committer;
    }

    public function getFiles(): array
    {
       return FileDTO::fromArray($this->files);
    }

}
