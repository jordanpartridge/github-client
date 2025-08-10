<?php

namespace JordanPartridge\GithubClient\Data\Commits;

use JordanPartridge\GithubClient\Data\GitUserData;

class CommitData
{
    public function __construct(
        public string $sha,
        public string $node_id,
        public CommitDetailsData $commit,
        public string $url,
        public string $html_url,
        public string $comments_url,
        public ?GitUserData $author,
        public ?GitUserData $committer,
        public array $parents,
        public ?CommitStatsData $stats = null,
        public ?array $files = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sha: $data['sha'],
            node_id: $data['node_id'],
            commit: CommitDetailsData::fromArray($data['commit']),
            url: $data['url'],
            html_url: $data['html_url'],
            comments_url: $data['comments_url'],
            author: isset($data['author']) ? GitUserData::fromArray($data['author']) : null,
            committer: isset($data['committer']) ? GitUserData::fromArray($data['committer']) : null,
            parents: $data['parents'] ?? [],
            stats: isset($data['stats']) ? CommitStatsData::fromArray($data['stats']) : null,
            files: isset($data['files']) ? array_map(
                fn (array $file) => CommitFileData::fromArray($file),
                $data['files'],
            ) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'sha' => $this->sha,
            'node_id' => $this->node_id,
            'commit' => $this->commit->toArray(),
            'url' => $this->url,
            'html_url' => $this->html_url,
            'comments_url' => $this->comments_url,
            'author' => $this->author?->toArray(),
            'committer' => $this->committer?->toArray(),
            'parents' => $this->parents,
            'stats' => $this->stats?->toArray(),
            'files' => $this->files ? array_map(fn (CommitFileData $file) => $file->toArray(), $this->files) : null,
        ];
    }
}
