<?php

namespace JordanPartridge\GithubClient\DTO\Users;

use JordanPartridge\GithubClient\DTO\Abstract;
use Spatie\LaravelData\Attributes\Validation;

class User extends Abstract
{
    public function __construct(
        #[Validation('required|integer')]
        public readonly int $id,

        #[Validation('required|string')]
        public readonly string $login,

        #[Validation('required|string')]
        public readonly string $avatar_url,

        #[Validation('required|string')]
        public readonly string $url,

        #[Validation('required|string')]
        public readonly string $html_url,

        #[Validation('required|string')]
        public readonly string $type,

        #[Validation('required|boolean')]
        public readonly bool $site_admin,

        #[Validation('nullable|string')]
        public readonly ?string $name = null,

        #[Validation('nullable|string|email')]
        public readonly ?string $email = null,

        #[Validation('nullable|string')]
        public readonly ?string $blog = null,

        #[Validation('nullable|string')]
        public readonly ?string $location = null,

        #[Validation('nullable|string')]
        public readonly ?string $bio = null,

        #[Validation('nullable|string')]
        public readonly ?string $twitter_username = null,

        #[Validation('nullable|integer')]
        public readonly ?int $public_repos = 0,

        #[Validation('nullable|integer')]
        public readonly ?int $followers = 0,

        #[Validation('nullable|integer')]
        public readonly ?int $following = 0,
    ) {}

    public static function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'login' => ['required', 'string'],
            'avatar_url' => ['required', 'string'],
            'url' => ['required', 'string'],
            'html_url' => ['required', 'string'],
            'type' => ['required', 'string'],
            'site_admin' => ['required', 'boolean'],
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email'],
            'blog' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'twitter_username' => ['nullable', 'string'],
            'public_repos' => ['nullable', 'integer'],
            'followers' => ['nullable', 'integer'],
            'following' => ['nullable', 'integer'],
        ];
    }
}