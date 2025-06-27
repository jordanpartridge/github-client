<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Pulls\Sort;
use JordanPartridge\GithubClient\Enums\Pulls\State;

class Params
{
    public function __construct(
        public readonly ?State $state,
        public readonly ?string $head,
        public readonly ?string $base,
        public readonly ?Sort $sort,
        public readonly ?Direction $direction,
        public readonly ?string $per_page,
        public readonly ?string $page,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            state: isset($data['state']) ? State::from($data['state']) : null,
            head: $data['head'] ?? null,
            base: $data['base'] ?? null,
            sort: isset($data['sort']) ? Sort::from($data['sort']) : null,
            direction: isset($data['direction']) ? Direction::from($data['direction']) : null,
            per_page: $data['per_page'] ?? null,
            page: $data['page'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'state' => $this->state?->value,
            'head' => $this->head,
            'base' => $this->base,
            'sort' => $this->sort?->value,
            'direction' => $this->direction?->value,
            'per_page' => $this->per_page,
            'page' => $this->page,
        ];
    }
}
