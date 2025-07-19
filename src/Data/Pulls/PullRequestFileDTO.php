<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

/**
 * Pull Request File DTO representing a single file change in a PR.
 *
 * This DTO contains detailed information about each file modified, added, or deleted
 * in a pull request, including change statistics and diff content.
 *
 * Perfect for AI-powered code analysis, security assessments, and change impact analysis.
 */
class PullRequestFileDTO
{
    public function __construct(
        public readonly string $sha,
        public readonly string $filename,
        public readonly string $status,
        public readonly int $additions,
        public readonly int $deletions,
        public readonly int $changes,
        public readonly string $blob_url,
        public readonly string $raw_url,
        public readonly string $contents_url,
        public readonly ?string $patch = null,
        public readonly ?string $previous_filename = null,
    ) {}

    /**
     * Create DTO from GitHub API response.
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            sha: $data['sha'],
            filename: $data['filename'],
            status: $data['status'],
            additions: $data['additions'],
            deletions: $data['deletions'],
            changes: $data['changes'],
            blob_url: $data['blob_url'],
            raw_url: $data['raw_url'],
            contents_url: $data['contents_url'],
            patch: $data['patch'] ?? null,
            previous_filename: $data['previous_filename'] ?? null,
        );
    }

    /**
     * Convert to array representation.
     */
    public function toArray(): array
    {
        return [
            'sha' => $this->sha,
            'filename' => $this->filename,
            'status' => $this->status,
            'additions' => $this->additions,
            'deletions' => $this->deletions,
            'changes' => $this->changes,
            'blob_url' => $this->blob_url,
            'raw_url' => $this->raw_url,
            'contents_url' => $this->contents_url,
            'patch' => $this->patch,
            'previous_filename' => $this->previous_filename,
        ];
    }

    // === UTILITY METHODS FOR ANALYSIS ===

    /**
     * Check if this file was added.
     */
    public function isAdded(): bool
    {
        return $this->status === 'added';
    }

    /**
     * Check if this file was deleted.
     */
    public function isDeleted(): bool
    {
        return $this->status === 'removed';
    }

    /**
     * Check if this file was modified.
     */
    public function isModified(): bool
    {
        return $this->status === 'modified';
    }

    /**
     * Check if this file was renamed.
     */
    public function isRenamed(): bool
    {
        return $this->status === 'renamed';
    }

    /**
     * Get the file extension.
     */
    public function getExtension(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * Get the directory path of the file.
     */
    public function getDirectory(): string
    {
        return dirname($this->filename);
    }

    /**
     * Get the base filename without extension.
     */
    public function getBasename(): string
    {
        return pathinfo($this->filename, PATHINFO_FILENAME);
    }

    /**
     * Calculate the change ratio (additions / total changes).
     */
    public function getAdditionRatio(): float
    {
        return $this->changes > 0 ? $this->additions / $this->changes : 0.0;
    }

    /**
     * Calculate the deletion ratio (deletions / total changes).
     */
    public function getDeletionRatio(): float
    {
        return $this->changes > 0 ? $this->deletions / $this->changes : 0.0;
    }

    /**
     * Check if this is a large file change (configurable threshold).
     */
    public function isLargeChange(int $threshold = 100): bool
    {
        return $this->changes >= $threshold;
    }

    /**
     * Check if this file has only additions (new content).
     */
    public function hasOnlyAdditions(): bool
    {
        return $this->additions > 0 && $this->deletions === 0;
    }

    /**
     * Check if this file has only deletions (removed content).
     */
    public function hasOnlyDeletions(): bool
    {
        return $this->deletions > 0 && $this->additions === 0;
    }

    /**
     * Detect file type based on extension.
     */
    public function getFileType(): string
    {
        $extension = strtolower($this->getExtension());
        $basename = strtolower($this->getBasename());
        $filename = strtolower(basename($this->filename));

        // Handle files with no extension by filename
        if (empty($extension)) {
            $specialFiles = [
                'dockerfile' => 'docker',
                'makefile' => 'makefile',
                'rakefile' => 'ruby',
                'gemfile' => 'ruby',
                'vagrantfile' => 'vagrant',
            ];

            if (isset($specialFiles[$filename])) {
                return $specialFiles[$filename];
            }
        }

        $typeMap = [
            // Programming languages
            'php' => 'php',
            'js' => 'javascript',
            'ts' => 'typescript',
            'py' => 'python',
            'java' => 'java',
            'kt' => 'kotlin',
            'swift' => 'swift',
            'rb' => 'ruby',
            'go' => 'go',
            'rs' => 'rust',
            'cpp' => 'cpp',
            'c' => 'c',
            'cs' => 'csharp',

            // Web technologies
            'html' => 'html',
            'css' => 'css',
            'scss' => 'sass',
            'less' => 'less',
            'vue' => 'vue',
            'jsx' => 'react',
            'tsx' => 'react-typescript',

            // Data formats
            'json' => 'json',
            'xml' => 'xml',
            'yaml' => 'yaml',
            'yml' => 'yaml',
            'toml' => 'toml',
            'ini' => 'config',

            // Documentation
            'md' => 'markdown',
            'rst' => 'restructuredtext',
            'txt' => 'text',

            // Configuration
            'env' => 'environment',
            'dockerfile' => 'docker',
            'gitignore' => 'gitignore',

            // Database
            'sql' => 'sql',
            'migration' => 'migration',
        ];

        return $typeMap[$extension] ?? 'unknown';
    }

    /**
     * Check if this is a test file.
     */
    public function isTestFile(): bool
    {
        $filename = strtolower($this->filename);

        return str_contains($filename, 'test') ||
               str_contains($filename, 'spec') ||
               str_contains(dirname($filename), 'test') ||
               str_contains(dirname($filename), '__test');
    }

    /**
     * Check if this is a configuration file.
     */
    public function isConfigFile(): bool
    {
        $extension = strtolower($this->getExtension());
        $basename = strtolower($this->getBasename());
        $filename = strtolower($this->filename);

        $configExtensions = ['env', 'ini', 'toml', 'yaml', 'yml', 'conf'];
        $configNames = ['config', 'configuration', 'settings', 'options'];

        return in_array($extension, $configExtensions) ||
               in_array($basename, $configNames) ||
               str_contains($filename, 'config') ||
               str_contains($filename, '.env') ||
               str_contains($basename, 'env');
    }

    /**
     * Check if this is a documentation file.
     */
    public function isDocumentationFile(): bool
    {
        $extension = strtolower($this->getExtension());
        $basename = strtolower($this->getBasename());

        $docExtensions = ['md', 'rst', 'txt'];
        $docNames = ['readme', 'changelog', 'license', 'contributing', 'docs'];

        return in_array($extension, $docExtensions) ||
               in_array($basename, $docNames) ||
               str_contains(dirname($this->filename), 'doc');
    }

    /**
     * Get a summary of this file change for display.
     */
    public function getSummary(): array
    {
        return [
            'file' => $this->filename,
            'status' => $this->status,
            'changes' => "+{$this->additions}/-{$this->deletions}",
            'type' => $this->getFileType(),
            'size' => $this->isLargeChange() ? 'large' : 'normal',
        ];
    }

    /**
     * Get analysis tags for this file change.
     */
    public function getAnalysisTags(): array
    {
        $tags = [];

        if ($this->isTestFile()) {
            $tags[] = 'test';
        }
        if ($this->isConfigFile()) {
            $tags[] = 'config';
        }
        if ($this->isDocumentationFile()) {
            $tags[] = 'docs';
        }
        if ($this->isLargeChange()) {
            $tags[] = 'large-change';
        }
        if ($this->hasOnlyAdditions()) {
            $tags[] = 'only-additions';
        }
        if ($this->hasOnlyDeletions()) {
            $tags[] = 'only-deletions';
        }
        if ($this->isRenamed()) {
            $tags[] = 'renamed';
        }

        $tags[] = $this->getFileType();
        $tags[] = $this->status;

        return $tags;
    }
}

