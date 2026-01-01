<?php

use JordanPartridge\GithubClient\Auth\TokenResolver;
use Illuminate\Support\Facades\Process;

describe('TokenResolver', function () {
    beforeEach(function () {
        // Store and clear original environment values
        $this->originalGithubToken = getenv('GITHUB_TOKEN');
        $this->originalGhToken = getenv('GH_TOKEN');

        // Clear environment variables completely (using false to unset)
        putenv('GITHUB_TOKEN');
        putenv('GH_TOKEN');

        // Also clear from $_ENV and $_SERVER which Laravel's env() checks
        unset($_ENV['GITHUB_TOKEN'], $_ENV['GH_TOKEN']);
        unset($_SERVER['GITHUB_TOKEN'], $_SERVER['GH_TOKEN']);

        // Reset the static lastSource property
        $reflection = new ReflectionClass(TokenResolver::class);
        $property = $reflection->getProperty('lastSource');
        $property->setAccessible(true);
        $property->setValue(null, null);
    });

    afterEach(function () {
        // Restore original values
        if ($this->originalGithubToken !== false) {
            putenv("GITHUB_TOKEN={$this->originalGithubToken}");
        }
        if ($this->originalGhToken !== false) {
            putenv("GH_TOKEN={$this->originalGhToken}");
        }
    });

    describe('resolve', function () {
        it('returns null when no token is available', function () {
            config()->set('github-client.token', null);

            // Mock Process to simulate gh CLI not available
            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $token = TokenResolver::resolve();

            expect($token)->toBeNull();
        });

        it('returns GITHUB_TOKEN from environment when set', function () {
            putenv('GITHUB_TOKEN=ghp_env_token_12345');
            $_ENV['GITHUB_TOKEN'] = 'ghp_env_token_12345';
            config()->set('github-client.token', null);

            // Mock Process to simulate gh CLI not available
            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $token = TokenResolver::resolve();

            expect($token)->toBe('ghp_env_token_12345');
        });

        it('returns GH_TOKEN from environment when GITHUB_TOKEN is not set', function () {
            putenv('GH_TOKEN=ghp_gh_token_12345');
            $_ENV['GH_TOKEN'] = 'ghp_gh_token_12345';
            config()->set('github-client.token', null);

            // Mock Process to simulate gh CLI not available
            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $token = TokenResolver::resolve();

            expect($token)->toBe('ghp_gh_token_12345');
        });

        it('returns config token when env vars are not set', function () {
            config()->set('github-client.token', 'ghp_config_token_12345');

            // Mock Process to simulate gh CLI not available
            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $token = TokenResolver::resolve();

            expect($token)->toBe('ghp_config_token_12345');
        });

        it('ignores placeholder token in config', function () {
            config()->set('github-client.token', 'your-github-token-here');

            // Mock Process to simulate gh CLI not available
            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $token = TokenResolver::resolve();

            expect($token)->toBeNull();
        });

        it('prefers GitHub CLI token over environment variables', function () {
            putenv('GITHUB_TOKEN=ghp_env_token');
            $_ENV['GITHUB_TOKEN'] = 'ghp_env_token';
            config()->set('github-client.token', null);

            // Mock Process to simulate gh CLI available and authenticated
            Process::fake([
                'which gh' => Process::result(output: '/usr/bin/gh', exitCode: 0),
                'gh auth token' => Process::result(output: 'ghp_cli_token_12345', exitCode: 0),
            ]);

            $token = TokenResolver::resolve();

            expect($token)->toBe('ghp_cli_token_12345');
        });

        it('handles gh CLI not authenticated', function () {
            putenv('GITHUB_TOKEN=ghp_env_fallback');
            $_ENV['GITHUB_TOKEN'] = 'ghp_env_fallback';
            config()->set('github-client.token', null);

            // Mock Process to simulate gh CLI available but not authenticated
            Process::fake([
                'which gh' => Process::result(output: '/usr/bin/gh', exitCode: 0),
                'gh auth token' => Process::result(output: '', exitCode: 1),
            ]);

            $token = TokenResolver::resolve();

            expect($token)->toBe('ghp_env_fallback');
        });

        it('handles gh auth token returning empty output', function () {
            putenv('GITHUB_TOKEN=ghp_fallback');
            $_ENV['GITHUB_TOKEN'] = 'ghp_fallback';
            config()->set('github-client.token', null);

            // Mock Process to simulate gh CLI returning whitespace only
            Process::fake([
                'which gh' => Process::result(output: '/usr/bin/gh', exitCode: 0),
                'gh auth token' => Process::result(output: '   ', exitCode: 0),
            ]);

            $token = TokenResolver::resolve();

            expect($token)->toBe('ghp_fallback');
        });
    });

    describe('hasAuthentication', function () {
        it('returns true when token is available', function () {
            putenv('GITHUB_TOKEN=ghp_test_token');
            $_ENV['GITHUB_TOKEN'] = 'ghp_test_token';
            config()->set('github-client.token', null);

            // Mock Process to simulate gh CLI not available
            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            expect(TokenResolver::hasAuthentication())->toBeTrue();
        });

        it('returns false when no token is available', function () {
            config()->set('github-client.token', null);

            // Mock Process to simulate gh CLI not available
            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            expect(TokenResolver::hasAuthentication())->toBeFalse();
        });
    });

    describe('getAuthenticationStatus', function () {
        it('returns status for GitHub CLI authentication', function () {
            config()->set('github-client.token', null);

            Process::fake([
                'which gh' => Process::result(output: '/usr/bin/gh', exitCode: 0),
                'gh auth token' => Process::result(output: 'ghp_cli_token', exitCode: 0),
            ]);

            $status = TokenResolver::getAuthenticationStatus();

            expect($status)->toBe('Authenticated via GitHub CLI');
        });

        it('returns status for GITHUB_TOKEN environment variable', function () {
            putenv('GITHUB_TOKEN=ghp_env_token');
            $_ENV['GITHUB_TOKEN'] = 'ghp_env_token';
            config()->set('github-client.token', null);

            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $status = TokenResolver::getAuthenticationStatus();

            expect($status)->toBe('Authenticated via environment variable (GITHUB_TOKEN)');
        });

        it('returns status for GH_TOKEN environment variable', function () {
            putenv('GH_TOKEN=ghp_gh_token');
            $_ENV['GH_TOKEN'] = 'ghp_gh_token';
            config()->set('github-client.token', null);

            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $status = TokenResolver::getAuthenticationStatus();

            expect($status)->toBe('Authenticated via environment variable (GH_TOKEN)');
        });

        it('returns status for config file authentication', function () {
            config()->set('github-client.token', 'ghp_config_token');

            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $status = TokenResolver::getAuthenticationStatus();

            expect($status)->toBe('Authenticated via config file');
        });

        it('returns no authentication status when no token available', function () {
            config()->set('github-client.token', null);

            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            $status = TokenResolver::getAuthenticationStatus();

            expect($status)->toBe('No authentication (public access only)');
        });
    });

    describe('getAuthenticationHelp', function () {
        it('returns helpful authentication guidance', function () {
            $help = TokenResolver::getAuthenticationHelp();

            expect($help)->toBeString()
                ->and($help)->toContain('GitHub CLI')
                ->and($help)->toContain('gh auth login')
                ->and($help)->toContain('GITHUB_TOKEN')
                ->and($help)->toContain('config/github-client.php')
                ->and($help)->toContain('rate limits');
        });
    });

    describe('getLastSource', function () {
        it('returns null before any resolution', function () {
            expect(TokenResolver::getLastSource())->toBeNull();
        });

        it('returns GitHub CLI as source', function () {
            config()->set('github-client.token', null);

            Process::fake([
                'which gh' => Process::result(output: '/usr/bin/gh', exitCode: 0),
                'gh auth token' => Process::result(output: 'ghp_cli_token', exitCode: 0),
            ]);

            TokenResolver::resolve();

            expect(TokenResolver::getLastSource())->toBe('GitHub CLI');
        });

        it('returns GITHUB_TOKEN as source', function () {
            putenv('GITHUB_TOKEN=ghp_env_token');
            $_ENV['GITHUB_TOKEN'] = 'ghp_env_token';
            config()->set('github-client.token', null);

            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            TokenResolver::resolve();

            expect(TokenResolver::getLastSource())->toBe('GITHUB_TOKEN');
        });

        it('returns GH_TOKEN as source', function () {
            putenv('GH_TOKEN=ghp_gh_token');
            $_ENV['GH_TOKEN'] = 'ghp_gh_token';
            config()->set('github-client.token', null);

            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            TokenResolver::resolve();

            expect(TokenResolver::getLastSource())->toBe('GH_TOKEN');
        });

        it('returns config as source', function () {
            config()->set('github-client.token', 'ghp_config_token');

            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            TokenResolver::resolve();

            expect(TokenResolver::getLastSource())->toBe('config');
        });

        it('returns null as source when no token found', function () {
            config()->set('github-client.token', null);

            Process::fake([
                'which gh' => Process::result(output: '', exitCode: 1),
            ]);

            TokenResolver::resolve();

            expect(TokenResolver::getLastSource())->toBeNull();
        });
    });
});
