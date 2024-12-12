<?php

namespace JordanPartridge\GithubClient\DTO\Repositories;

use JordanPartridge\GithubClient\DTO\Abstract;
use JordanPartridge\GithubClient\DTO\Users\User;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation;

class Repository extends Abstract
{
    public function __construct(
        #[Validation('required|integer')]
        public readonly int $id,

        #[Validation('required|string')]
        public readonly string $name,

        #[Validation('required|string')]
        public readonly string $full_name,

        #[Validation('required')]
        public readonly User $owner,

        #[Validation('required|boolean')]
        public readonly bool $private,

        #[Validation('nullable|string')]
        public readonly ?string $description,

        #[Validation('required|boolean')]
        public readonly bool $fork,

        #[Validation('nullable|string')]
        public readonly ?string $language,

        #[Validation('required|string')]
        public readonly string $default_branch,

        #[Validation('nullable|array')]
        public readonly ?array $topics = null,

        #[Validation('nullable|integer')]
        public readonly ?int $stargazers_count = 0,

        #[Validation('nullable|integer')]
        public readonly ?int $watchers_count = 0,

        #[Validation('nullable|integer')]
        public readonly ?int $forks_count = 0,

        #[Validation('nullable|string|date')]
        public readonly ?string $created_at = null,

        #[Validation('nullable|string|date')]
        public readonly ?string $updated_at = null,

        #[Validation('nullable|string|date')]
        public readonly ?string $pushed_at = null,
    ) {}

    public static function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'full_name' => ['required', 'string'],
            'owner' => ['required'],
            'private' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
            'fork' => ['required', 'boolean'],
            'language' => ['nullable', 'string'],
            'default_branch' => ['required', 'string'],
            'topics' => ['nullable', 'array'],
            'stargazers_count' => ['nullable', 'integer'],
            'watchers_count' => ['nullable', 'integer'],
            'forks_count' => ['nullable', 'integer'],
            'created_at' => ['nullable', 'string', 'date'],
            'updated_at' => ['nullable', 'string', 'date'],
            'pushed_at' => ['nullable', 'string', 'date'],
        ];
    }
}