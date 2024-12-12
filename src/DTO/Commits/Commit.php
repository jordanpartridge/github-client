<?php

namespace JordanPartridge\GithubClient\DTO\Commits;

use JordanPartridge\GithubClient\DTO\Abstract;
use JordanPartridge\GithubClient\DTO\Users\User;
use Spatie\LaravelData\Attributes\Validation;

class Commit extends Abstract
{
    public function __construct(
        #[Validation('required|string')]
        public readonly string $sha,

        #[Validation('required')]
        public readonly CommitDetail $commit,

        #[Validation('required')]
        public readonly User $author,

        #[Validation('required')]
        public readonly User $committer,

        #[Validation('nullable|array')]
        public readonly ?array $parents = null,

        #[Validation('nullable')]
        public readonly ?CommitStats $stats = null,

        #[Validation('nullable|array')]
        public readonly ?array $files = null,
    ) {}

    public static function rules(): array
    {
        return [
            'sha' => ['required', 'string'],
            'commit' => ['required'],
            'author' => ['required'],
            'committer' => ['required'],
            'parents' => ['nullable', 'array'],
            'stats' => ['nullable'],
            'files' => ['nullable', 'array'],
        ];
    }
}