<?php

namespace JordanPartridge\GithubClient\DTO\Commits;

use JordanPartridge\GithubClient\DTO\Abstract;
use Spatie\LaravelData\Attributes\Validation;

class CommitStats extends Abstract
{
    public function __construct(
        #[Validation('required|integer')]
        public readonly int $total,

        #[Validation('required|integer')]
        public readonly int $additions,

        #[Validation('required|integer')]
        public readonly int $deletions,
    ) {}

    public static function rules(): array
    {
        return [
            'total' => ['required', 'integer'],
            'additions' => ['required', 'integer'],
            'deletions' => ['required', 'integer'],
        ];
    }
}