<?php

use JordanPartridge\GithubClient\Auth\TokenResolver;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;

describe('TokenResolver', function () {
    describe('resolve method', function () {
        it('returns null when no token found and not required', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return false;
                }

                protected function getEnvironmentToken(): ?string
                {
                    return null;
                }

                protected function getConfigToken(): ?string
                {
                    return null;
                }
            };

            expect($resolver->resolve(required: false))->toBeNull();
        });

        it('throws exception when required but no token found', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return false;
                }

                protected function getEnvironmentToken(): ?string
                {
                    return null;
                }

                protected function getConfigToken(): ?string
                {
                    return null;
                }
            };

            expect(fn () => $resolver->resolve(required: true))
                ->toThrow(AuthenticationException::class);
        });

        it('prioritizes GitHub CLI token over environment variables', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return true;
                }

                protected function getGitHubCliToken(): ?string
                {
                    return 'gh_cli_token';
                }

                protected function getEnvironmentToken(): ?string
                {
                    return 'env_token';
                }

                protected function getConfigToken(): ?string
                {
                    return 'config_token';
                }
            };

            expect($resolver->resolve())->toBe('gh_cli_token');
        });

        it('uses environment token when CLI not available', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return false;
                }

                protected function getEnvironmentToken(): ?string
                {
                    return 'env_token';
                }

                protected function getConfigToken(): ?string
                {
                    return 'config_token';
                }
            };

            expect($resolver->resolve())->toBe('env_token');
        });

        it('uses config token as fallback', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return false;
                }

                protected function getEnvironmentToken(): ?string
                {
                    return null;
                }

                protected function getConfigToken(): ?string
                {
                    return 'config_token';
                }
            };

            expect($resolver->resolve())->toBe('config_token');
        });
    });

    describe('hasGitHubCliAuth method', function () {
        it('returns true when GitHub CLI is authenticated', function () {
            $resolver = new class () extends TokenResolver {
                protected function getGitHubCliToken(): ?string
                {
                    return 'some_token';
                }
            };

            expect($resolver->hasGitHubCliAuth())->toBeTrue();
        });

        it('returns false when GitHub CLI is not authenticated', function () {
            $resolver = new class () extends TokenResolver {
                protected function getGitHubCliToken(): ?string
                {
                    return null;
                }
            };

            expect($resolver->hasGitHubCliAuth())->toBeFalse();
        });
    });

    describe('getAuthenticationGuidance method', function () {
        it('includes GitHub CLI in guidance when available', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return true;
                }
            };

            $guidance = $resolver->getAuthenticationGuidance();

            expect($guidance)->toContain('gh auth login')
                ->and($guidance)->toContain('GITHUB_TOKEN')
                ->and($guidance)->toContain('GH_TOKEN')
                ->and($guidance)->toContain('config/github-client.php')
                ->and($guidance)->toContain('public repositories');
        });

        it('suggests installing GitHub CLI when not available', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return false;
                }
            };

            $guidance = $resolver->getAuthenticationGuidance();

            expect($guidance)->toContain('Install GitHub CLI');
        });
    });

    describe('error handling', function () {
        it('includes helpful guidance in exception message', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return false;
                }

                protected function getEnvironmentToken(): ?string
                {
                    return null;
                }

                protected function getConfigToken(): ?string
                {
                    return null;
                }
            };

            try {
                $resolver->resolve(required: true);
                expect(false)->toBeTrue('Exception should have been thrown');
            } catch (AuthenticationException $e) {
                expect($e->getMessage())
                    ->toContain('No GitHub authentication found')
                    ->and($e->getMessage())->toContain('gh auth login');
            }
        });

        it('has correct exception code for required authentication', function () {
            $resolver = new class () extends TokenResolver {
                protected function isGitHubCliAvailable(): bool
                {
                    return false;
                }

                protected function getEnvironmentToken(): ?string
                {
                    return null;
                }

                protected function getConfigToken(): ?string
                {
                    return null;
                }
            };

            try {
                $resolver->resolve(required: true);
                expect(false)->toBeTrue('Should have thrown exception');
            } catch (AuthenticationException $e) {
                expect($e->getCode())->toBe(400);
            }
        });
    });
});
