<?php

namespace JordanPartridge\GithubClient\Data\Repos;

class SearchRepositoriesData
{
    /**
     * @param  int  $total_count  Total number of repositories found
     * @param  bool  $incomplete_results  Whether the results are incomplete
     * @param  RepoData[]  $items  Array of repository data objects
     */
    public function __construct(
        public int $total_count,
        public bool $incomplete_results,
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total_count: $data['total_count'],
            incomplete_results: $data['incomplete_results'],
            items: array_map(
                fn (array $item) => RepoData::fromArray($item),
                $data['items'] ?? []
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'total_count' => $this->total_count,
            'incomplete_results' => $this->incomplete_results,
            'items' => array_map(fn (RepoData $item) => $item->toArray(), $this->items),
        ];
    }
}
