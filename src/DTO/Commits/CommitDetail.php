<?php

namespace JordanPartridge\GithubClient\DTO\Commits;

use JordanPartridge\GithubClient\DTO\Abstract;
use Spatie\LaravelData\Attributes\Validation;

class CommitDetail extends Abstract
{
    public function __construct(
        #[Validation('required|string')]
        public readonly string $message,

        #[Validation('required|array')]
        public readonly array $author,

        #[Validation('required|array')]
        public readonly array $committer,

        #[Validation('nullable|array')]
        public readonly ?array $verification = null,
    ) {}

    public static function rules(): array
    {
        return [
            'message' => ['required', 'string'],
            'author' => ['required', 'array'],
            'committer' => ['required', 'array'],
            'verification' => ['nullable', 'array'],
        ];
    }
}