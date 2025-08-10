<?php

namespace JordanPartridge\GithubClient\Data\Releases;

use Carbon\Carbon;
use JordanPartridge\GithubClient\Data\GitUserData;

class ReleaseData
{
    public function __construct(
        public string $url,
        public string $assets_url,
        public string $upload_url,
        public string $html_url,
        public int $id,
        public GitUserData $author,
        public string $node_id,
        public string $tag_name,
        public string $target_commitish,
        public string $name,
        public bool $draft,
        public bool $prerelease,
        public Carbon $created_at,
        public Carbon $published_at,
        public array $assets,
        public string $tarball_url,
        public string $zipball_url,
        public ?string $body,
        public ?string $discussion_url = null,
        public ?bool $make_latest = null,
        public ?array $reactions = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'],
            assets_url: $data['assets_url'],
            upload_url: $data['upload_url'],
            html_url: $data['html_url'],
            id: $data['id'],
            author: GitUserData::fromArray($data['author']),
            node_id: $data['node_id'],
            tag_name: $data['tag_name'],
            target_commitish: $data['target_commitish'],
            name: $data['name'],
            draft: $data['draft'],
            prerelease: $data['prerelease'],
            created_at: Carbon::parse($data['created_at']),
            published_at: Carbon::parse($data['published_at']),
            assets: $data['assets'] ?? [],
            tarball_url: $data['tarball_url'],
            zipball_url: $data['zipball_url'],
            body: $data['body'] ?? null,
            discussion_url: $data['discussion_url'] ?? null,
            make_latest: $data['make_latest'] ?? null,
            reactions: $data['reactions'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'assets_url' => $this->assets_url,
            'upload_url' => $this->upload_url,
            'html_url' => $this->html_url,
            'id' => $this->id,
            'author' => $this->author->toArray(),
            'node_id' => $this->node_id,
            'tag_name' => $this->tag_name,
            'target_commitish' => $this->target_commitish,
            'name' => $this->name,
            'draft' => $this->draft,
            'prerelease' => $this->prerelease,
            'created_at' => $this->created_at->toISOString(),
            'published_at' => $this->published_at->toISOString(),
            'assets' => $this->assets,
            'tarball_url' => $this->tarball_url,
            'zipball_url' => $this->zipball_url,
            'body' => $this->body,
            'discussion_url' => $this->discussion_url,
            'make_latest' => $this->make_latest,
            'reactions' => $this->reactions,
        ];
    }
}
