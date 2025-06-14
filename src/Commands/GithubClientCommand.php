<?php

namespace JordanPartridge\GithubClient\Commands;

use Illuminate\Console\Command;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\ValueObjects\Repo;

class GithubClientCommand extends Command
{
    public $signature = 'github-client 
                        {action : The action to perform (test, repo, commits)}
                        {--repo= : Repository name (owner/repo)}
                        {--limit=5 : Number of items to display}';

    public $description = 'GitHub Client utility commands';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'test' => $this->testConnection(),
            'repo' => $this->showRepositoryInfo(),
            'commits' => $this->showCommits(),
            default => $this->showHelp(),
        };
    }

    private function testConnection(): int
    {
        $this->info('Testing GitHub API connection...');

        try {
            $repos = Github::repos()->all(per_page: 1);
            $this->info('✅ Connection successful!');
            $this->line('API token is valid and working.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Connection failed: '.$e->getMessage());

            if (str_contains($e->getMessage(), 'token')) {
                $this->warn('💡 Make sure GITHUB_TOKEN is set in your .env file');
            }

            return self::FAILURE;
        }
    }

    private function showRepositoryInfo(): int
    {
        $repo = $this->option('repo');

        if (! $repo) {
            $this->error('Repository name is required. Use --repo=owner/repo');

            return self::FAILURE;
        }

        try {
            $this->info("Fetching repository information for: {$repo}");
            $repoData = Github::repos()->get(Repo::fromFullName($repo));

            $this->table(['Property', 'Value'], [
                ['Name', $repoData->name],
                ['Full Name', $repoData->full_name],
                ['Description', $repoData->description ?? 'No description'],
                ['Language', $repoData->language ?? 'Not specified'],
                ['Stars', $repoData->stargazers_count],
                ['Forks', $repoData->forks_count],
                ['Open Issues', $repoData->open_issues_count],
                ['Created', $repoData->created_at->format('Y-m-d H:i:s')],
                ['Updated', $repoData->updated_at->format('Y-m-d H:i:s')],
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to fetch repository: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function showCommits(): int
    {
        $repo = $this->option('repo');

        if (! $repo) {
            $this->error('Repository name is required. Use --repo=owner/repo');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');

        try {
            $this->info("Fetching recent commits for: {$repo}");
            $commits = Github::commits()->all($repo, per_page: $limit);

            $tableData = [];
            foreach ($commits as $commit) {
                $tableData[] = [
                    substr($commit->sha, 0, 8),
                    $commit->commit->author->name,
                    $commit->commit->author->date->format('Y-m-d H:i'),
                    \Illuminate\Support\Str::limit($commit->commit->message, 50),
                ];
            }

            $this->table(['SHA', 'Author', 'Date', 'Message'], $tableData);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to fetch commits: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function showHelp(): int
    {
        $this->info('GitHub Client Commands');
        $this->line('');
        $this->line('Available actions:');
        $this->line('  test     - Test GitHub API connection');
        $this->line('  repo     - Show repository information (requires --repo)');
        $this->line('  commits  - Show recent commits (requires --repo)');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan github-client test');
        $this->line('  php artisan github-client repo --repo=laravel/framework');
        $this->line('  php artisan github-client commits --repo=laravel/framework --limit=10');

        return self::SUCCESS;
    }
}
