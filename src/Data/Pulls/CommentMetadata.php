<?php

namespace JordanPartridge\GithubClient\Data\Pulls;

/**
 * Comment metadata extracted from AI reviewer and bot comments.
 */
class CommentMetadata
{
    public function __construct(
        public readonly ?string $severity = null,
        public readonly ?string $file_path = null,
        public readonly ?int $line_number = null,
        public readonly ?string $code_snippet = null,
        public readonly ?string $claim_type = null,
        public readonly ?string $reviewer_type = null,
        public readonly array $raw_patterns = [],
    ) {}

    public static function extract(string $body, ?string $path = null, ?int $position = null, ?string $author = null): self
    {
        $metadata = new self;

        return new self(
            severity: $metadata->extractSeverity($body),
            file_path: $path,
            line_number: $metadata->extractLineNumber($body, $position),
            code_snippet: $metadata->extractCodeSnippet($body),
            claim_type: $metadata->extractClaimType($body),
            reviewer_type: $metadata->determineReviewerType($author),
            raw_patterns: $metadata->extractRawPatterns($body),
        );
    }

    private function extractSeverity(string $body): ?string
    {
        // Priority patterns (most specific first)
        $severityPatterns = [
            // Explicit severity markers
            '/\[(?:SEVERITY|SEV):\s*(HIGH|MEDIUM|LOW)\]/i' => '$1',
            '/\b(?:severity|priority):\s*(high|medium|low|critical|warning|info)\b/i' => '$1',

            // Emoji-based severity
            '/🔴|❌|⛔/' => 'high',
            '/🟡|⚠️|⚠/' => 'medium',
            '/🟢|✅|ℹ️/' => 'low',

            // Keywords indicating severity
            '/\b(critical|security|vulnerability|exploit|dangerous)\b/i' => 'high',
            '/\b(warning|potential|possible|consider|recommend)\b/i' => 'medium',
            '/\b(suggestion|nit|style|formatting|minor)\b/i' => 'low',

            // CodeRabbit patterns
            '/\*\*Potential\s+(?:security\s+)?(?:vulnerability|issue)\*\*/i' => 'high',
            '/\*\*Warning\*\*/i' => 'medium',
            '/\*\*Suggestion\*\*/i' => 'low',

            // SonarQube patterns
            '/\bbug\b/i' => 'high',
            '/\bcode\s+smell\b/i' => 'medium',
            '/\binfo\b/i' => 'low',
        ];

        foreach ($severityPatterns as $pattern => $severity) {
            if (preg_match($pattern, $body, $matches)) {
                $result = str_replace('$1', $matches[1] ?? $severity, $severity);

                return strtolower($result);
            }
        }

        return null;
    }

    private function extractLineNumber(string $body, ?int $position): ?int
    {
        // If position is provided from GitHub API, use it
        if ($position !== null && $position > 0) {
            return $position;
        }

        // Extract from comment text
        $linePatterns = [
            '/\bline\s+(\d+)\b/i',
            '/\bL(\d+)\b/',
            '/\:(\d+)\:/',
            '/\#L(\d+)/',
        ];

        foreach ($linePatterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    private function extractCodeSnippet(string $body): ?string
    {
        // Extract code blocks
        if (preg_match('/```(?:\w+)?\n?(.*?)\n?```/s', $body, $matches)) {
            return trim($matches[1]);
        }

        // Extract inline code
        if (preg_match('/`([^`]+)`/', $body, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function extractClaimType(string $body): ?string
    {
        $claimPatterns = [
            '/\bnull\s+pointer\s+(?:exception|error|dereference)\b/i' => 'null_pointer_exception',
            '/\bmemory\s+leak\b/i' => 'memory_leak',
            '/\bsql\s+injection\b/i' => 'sql_injection',
            '/\bxss\b|cross.site.scripting/i' => 'xss',
            '/\brace\s+condition\b/i' => 'race_condition',
            '/\bdeadlock\b/i' => 'deadlock',
            '/\bunused\s+(?:variable|import|method)\b/i' => 'unused_code',
            '/\bcode\s+duplication\b/i' => 'code_duplication',
            '/\bcomplexity\b/i' => 'complexity',
            '/\bperformance\b/i' => 'performance',
            '/\bsecurity\b/i' => 'security',
            '/\btypo\b/i' => 'typo',
            '/\bstyle\b/i' => 'style',
        ];

        foreach ($claimPatterns as $pattern => $type) {
            if (preg_match($pattern, $body)) {
                return $type;
            }
        }

        return null;
    }

    private function determineReviewerType(?string $author = null): ?string
    {
        if (! $author) {
            return null;
        }

        $botPatterns = [
            '/coderabbitai/i' => 'coderabbit',
            '/sonarqubecloud/i' => 'sonarqube',
            '/github-actions/i' => 'github_actions',
            '/dependabot/i' => 'dependabot',
            '/codecov/i' => 'codecov',
            '/\[bot\]$/i' => 'bot',
        ];

        foreach ($botPatterns as $pattern => $type) {
            if (preg_match($pattern, $author)) {
                return $type;
            }
        }

        return 'human';
    }

    private function extractRawPatterns(string $body): array
    {
        $patterns = [];

        // Extract all markdown patterns
        if (preg_match_all('/\*\*([^*]+)\*\*/', $body, $matches)) {
            $patterns['bold'] = $matches[1];
        }

        if (preg_match_all('/(?<!\*)\*([^*]+)\*(?!\*)/', $body, $matches)) {
            $patterns['italic'] = $matches[1];
        }

        // Extract links
        if (preg_match_all('/\[([^\]]+)\]\(([^)]+)\)/', $body, $matches)) {
            $patterns['links'] = array_combine($matches[1], $matches[2]);
        }

        return $patterns;
    }

    public function toArray(): array
    {
        return [
            'severity' => $this->severity,
            'file_path' => $this->file_path,
            'line_number' => $this->line_number,
            'code_snippet' => $this->code_snippet,
            'claim_type' => $this->claim_type,
            'reviewer_type' => $this->reviewer_type,
            'raw_patterns' => $this->raw_patterns,
        ];
    }
}
