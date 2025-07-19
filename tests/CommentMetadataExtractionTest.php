<?php

use JordanPartridge\GithubClient\Data\Pulls\CommentMetadata;

describe('CommentMetadata', function () {
    it('extracts severity from explicit markers', function () {
        $body = '[SEVERITY: HIGH] This is a critical security vulnerability';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->severity)->toBe('high');
    });

    it('extracts severity from emoji patterns', function () {
        $body = '🔴 Critical: Potential null pointer exception detected';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->severity)->toBe('high');
    });

    it('extracts severity from CodeRabbit patterns', function () {
        $body = '**Potential security vulnerability** detected in this code block.';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->severity)->toBe('high');
    });

    it('extracts line numbers from comment text', function () {
        $body = 'Issue found at line 42 in this file';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->line_number)->toBe(42);
    });

    it('prefers position parameter over extracted line number', function () {
        $body = 'Issue found at line 42 in this file';
        $metadata = CommentMetadata::extract($body, null, 25);
        
        expect($metadata->line_number)->toBe(25);
    });

    it('extracts code snippets from markdown', function () {
        $body = 'The problematic code is: ```php return $user->name; ```';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->code_snippet)->toBe('return $user->name;');
    });

    it('extracts inline code snippets', function () {
        $body = 'The variable `$user->name` might be null here.';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->code_snippet)->toBe('$user->name');
    });

    it('identifies claim types from content', function () {
        $body = 'Potential null pointer exception in this method';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->claim_type)->toBe('null_pointer_exception');
    });

    it('determines reviewer type from author', function () {
        $metadata = CommentMetadata::extract('Some comment', null, null, 'coderabbitai');
        expect($metadata->reviewer_type)->toBe('coderabbit');
        
        $metadata = CommentMetadata::extract('Some comment', null, null, 'dependabot[bot]');
        expect($metadata->reviewer_type)->toBe('dependabot');
        
        $metadata = CommentMetadata::extract('Some comment', null, null, 'human-reviewer');
        expect($metadata->reviewer_type)->toBe('human');
    });

    it('extracts raw patterns for further analysis', function () {
        $body = '**Warning**: This code contains *potential* issues. See [docs](https://example.com)';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->raw_patterns)
            ->toHaveKey('bold')
            ->and($metadata->raw_patterns['bold'])->toContain('Warning')
            ->and($metadata->raw_patterns)->toHaveKey('italic')
            ->and($metadata->raw_patterns['italic'])->toContain('potential')
            ->and($metadata->raw_patterns)->toHaveKey('links')
            ->and($metadata->raw_patterns['links']['docs'])->toBe('https://example.com');
    });

    it('handles complex CodeRabbit comment format', function () {
        $body = '🔴 **Potential security vulnerability**

The code at line 125 contains a SQL injection risk:

```php
$query = "SELECT * FROM users WHERE id = " . $userId;
```

**Recommendation**: Use parameterized queries to prevent SQL injection attacks.

**Severity**: HIGH
**File**: app/Http/Controllers/UserController.php';

        $metadata = CommentMetadata::extract($body, 'app/Http/Controllers/UserController.php', 125, 'coderabbitai');
        
        expect($metadata->severity)->toBe('high')
            ->and($metadata->file_path)->toBe('app/Http/Controllers/UserController.php')
            ->and($metadata->line_number)->toBe(125)
            ->and($metadata->code_snippet)->toContain('SELECT * FROM users')
            ->and($metadata->claim_type)->toBe('sql_injection')
            ->and($metadata->reviewer_type)->toBe('coderabbit');
    });

    it('handles SonarQube comment patterns', function () {
        $body = 'Bug: This method complexity is too high (15 > 10 allowed)';
        $metadata = CommentMetadata::extract($body, null, null, 'sonarqubecloud');
        
        expect($metadata->severity)->toBe('high')  // "bug" maps to high
            ->and($metadata->claim_type)->toBe('complexity')
            ->and($metadata->reviewer_type)->toBe('sonarqube');
    });

    it('returns null values when no patterns match', function () {
        $body = 'This is just a regular comment with no special markers.';
        $metadata = CommentMetadata::extract($body);
        
        expect($metadata->severity)->toBeNull()
            ->and($metadata->claim_type)->toBeNull()
            ->and($metadata->code_snippet)->toBeNull()
            ->and($metadata->line_number)->toBeNull();
    });

    it('converts to array properly', function () {
        $metadata = CommentMetadata::extract(
            '🔴 **Critical**: SQL injection at line 42',
            'app/Models/User.php',
            42,
            'coderabbitai'
        );
        
        $array = $metadata->toArray();
        
        expect($array)
            ->toBeArray()
            ->toHaveKey('severity', 'high')
            ->toHaveKey('file_path', 'app/Models/User.php')
            ->toHaveKey('line_number', 42)
            ->toHaveKey('claim_type', 'sql_injection')
            ->toHaveKey('reviewer_type', 'coderabbit');
    });
});