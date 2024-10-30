# github-client 🚀

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jordanpartridge/github-client/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jordanpartridge/github-client/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jordanpartridge/github-client/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jordanpartridge/github-client/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jordanpartridge/github-client.svg?style=flat-square)](https://packagist.org/packages/jordanpartridge/github-client)

Time to `git push` your GitHub API game to the next level! A Laravel-first GitHub client built on Saloon that makes working with GitHub's API a breeze. Let's `git 'er done`!

## 🔥 Features

- Built on Saloon - the coolest way to handle APIs in Laravel
- Fully typed responses - no more guessing what you'll `git` back
- Ready to roll with Laravel's config & auth systems
- Test coverage that would make your CI/CD pipeline jealous
- Facades, dependency injection, or however you roll
- Modern PHP 8.1+ (because we're not living in the `dark ages` branch)

## 🚀 Quick Start

```bash
composer require jordanpartridge/github-client
```

First grab your GitHub token from [GitHub Settings](https://github.com/settings/tokens), then drop it in your `.env`:
```
GITHUB_TOKEN=your-token-here
```

Now you're ready to rock:
```php
use JordanPartridge\GithubClient\Facades\GitHub;

// Fetch repos faster than you can say "git clone"
$repo = GitHub::repository('jordanpartridge/github-client');
echo $repo->name; // 'github-client'

// Create issues without the issues
$issue = GitHub::issues()->create('jordanpartridge/github-client', [
    'title' => 'This is awesome',
    'body'  => 'But could be more awesome 🚀'
]);
```

Want dependency injection? We've got you:
```php
use JordanPartridge\GithubClient\Contracts\GitHub;

public function __construct(
    private readonly GitHub $github
) {}
```

## ⚙️ Configuration

Need to tweak something? Publish the config:
```bash
php artisan vendor:publish --tag="github-client-config"
```

## 📖 Documentation

Head over to [the docs](https://github.com/jordanpartridge/github-client#documentation) - they don't byte!

## 🧪 Testing

```bash
composer test
```

Because untested code is like an empty commit message - nobody wants that.

## 🤝 Contributing

Found a bug? `git commit` to fixing it with a PR!
Want a feature? `git checkout` our issues page!

Just remember to:
1. Add tests (we're not animals)
2. Follow PSR-12 (keep it clean)
3. Be awesome (you already are)

## 📝 License

MIT. `git checkout` whatever you need!

## 💖 Credits

`git blame` these awesome folks:
- [Jordan Partridge](https://github.com/jordanpartridge)
- [All Contributors](../../contributors)

Built with Saloon, Laravel love, and probably too much coffee ☕️
