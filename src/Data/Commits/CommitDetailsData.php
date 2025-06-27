<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use JordanPartridge\GithubClient\Data\FileDTO;
use JordanPartridge\GithubClient\Data\TreeData;
use JordanPartridge\GithubClient\Data\VerificationData;

class CommitDetailsData
{
    public function __construct(
        public CommitAuthorData $author,
        public CommitAuthorData $committer,
        public string $message,
        public TreeData $tree,
        public string $url,
        public int $comment_count,
        public VerificationData $verification,
        public ?array $files = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            author: CommitAuthorData::fromArray($data['author']),
            committer: CommitAuthorData::fromArray($data['committer']),
            message: $data['message'],
            tree: TreeData::fromArray($data['tree']),
            url: $data['url'],
            comment_count: $data['comment_count'],
            verification: VerificationData::fromArray($data['verification']),
            files: isset($data['files']) ? array_map(
                fn (array $file) => FileDTO::fromArray($file),
                $data['files']
            ) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'author' => $this->author->toArray(),
            'committer' => $this->committer->toArray(),
            'message' => $this->message,
            'tree' => $this->tree->toArray(),
            'url' => $this->url,
            'comment_count' => $this->comment_count,
            'verification' => $this->verification->toArray(),
            'files' => $this->files ? array_map(fn (FileDTO $file) => $file->toArray(), $this->files) : null,
        ];
    }
}
