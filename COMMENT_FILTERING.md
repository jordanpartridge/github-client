# Comment Filtering & Metadata Extraction

This document explains the new comment filtering and metadata extraction capabilities introduced in v2.7.0.

## Features

### 1. Enhanced Comment Fetching with Filtering

Get PR comments with advanced filtering options:

```php
use JordanPartridge\GithubClient\Facades\Github;

// Get all CodeRabbit comments from PR #42
$comments = Github::comments()->forPullRequest(42, [
    'repository' => 'owner/repo',
    'author' => 'coderabbitai'
]);

// Get all bot comments with high severity
$criticalIssues = Github::comments()->forPullRequest(42, [
    'repository' => 'owner/repo', 
    'author_type' => 'bot',
    'severity' => 'high'
]);

// Get comments from specific file
$fileComments = Github::comments()->forPullRequest(42, [
    'repository' => 'owner/repo',
    'file_path' => 'app/Http/Controllers/UserController.php'
]);
```

### 2. Comment Metadata Extraction

Automatically extract metadata from comment content:

```php
// Each comment now includes rich metadata
$comment = $comments[0];

echo $comment->metadata->severity;      // 'high', 'medium', 'low'
echo $comment->metadata->claim_type;    // 'sql_injection', 'null_pointer_exception', etc.
echo $comment->metadata->reviewer_type; // 'coderabbit', 'sonarqube', 'human', etc.
echo $comment->metadata->line_number;   // Extracted line number
echo $comment->metadata->code_snippet;  // Extracted code block
```

### 3. Convenient Shortcut Methods

```php
// Get all CodeRabbit comments
$coderabbitComments = Github::comments()->codeRabbit(42, [
    'repository' => 'owner/repo'
]);

// Get all bot comments (any AI reviewer)
$botComments = Github::comments()->bots(42, [
    'repository' => 'owner/repo'
]);

// Get human reviewer comments only
$humanComments = Github::comments()->humans(42, [
    'repository' => 'owner/repo'
]);

// Filter by author
$authorComments = Github::comments()->byAuthor(42, 'specific-user', [
    'repository' => 'owner/repo'
]);

// Filter by severity
$criticalComments = Github::comments()->bySeverity(42, 'high', [
    'repository' => 'owner/repo'
]);

// Get comments for specific file
$fileComments = Github::comments()->forFile(42, 'src/User.php', [
    'repository' => 'owner/repo'
]);
```

## Available Filters

### Author Filters
- `author` - Specific username (e.g., 'coderabbitai')
- `author_type` - 'bot' or 'human'

### Content Filters  
- `severity` - 'high', 'medium', 'low'
- `claim_type` - 'sql_injection', 'null_pointer_exception', 'security', etc.
- `contains` - Text search within comment body

### Location Filters
- `file_path` - Specific file path
- `repository` - Repository in 'owner/repo' format (required)

### Date Filters
- `since` - ISO date string (e.g., '2023-01-01T12:00:00Z')
- `until` - ISO date string

### Pagination
- `per_page` - Number of results per page (max 100)
- `page` - Page number

## Metadata Detection Patterns

### Severity Detection

The system automatically detects severity from various patterns:

**Explicit markers:**
- `[SEVERITY: HIGH]`
- `[SEV: MEDIUM]`
- `severity: low`

**Emoji indicators:**
- 🔴 ❌ ⛔ → high severity
- 🟡 ⚠️ → medium severity  
- 🟢 ✅ ℹ️ → low severity

**Keywords:**
- `critical`, `security`, `vulnerability` → high
- `warning`, `potential`, `consider` → medium
- `suggestion`, `nit`, `style` → low

**Tool-specific patterns:**
- CodeRabbit: `**Potential security vulnerability**` → high
- SonarQube: `Bug:` → high, `Code smell:` → medium

### Claim Type Detection

Automatically identifies types of issues:

- `null_pointer_exception` - Null pointer issues
- `sql_injection` - SQL injection vulnerabilities  
- `xss` - Cross-site scripting
- `memory_leak` - Memory management issues
- `race_condition` - Concurrency issues
- `security` - General security issues
- `performance` - Performance problems
- `complexity` - Code complexity issues
- `unused_code` - Unused variables/imports
- `style` - Code style issues

### Reviewer Type Detection

Identifies the type of reviewer:

- `coderabbit` - CodeRabbit AI
- `sonarqube` - SonarQube analysis
- `dependabot` - Dependabot
- `github_actions` - GitHub Actions
- `bot` - Generic bot
- `human` - Human reviewer

## Complete Example

```php
use JordanPartridge\GithubClient\Facades\Github;

// Get all high-severity security issues from CodeRabbit
$securityIssues = Github::comments()->forPullRequest(42, [
    'repository' => 'myorg/myproject',
    'author' => 'coderabbitai',
    'severity' => 'high',
    'claim_type' => 'security'
]);

foreach ($securityIssues as $comment) {
    echo "Security Issue in {$comment->path}:\n";
    echo "Line: {$comment->metadata->line_number}\n";
    echo "Severity: {$comment->metadata->severity}\n";
    echo "Type: {$comment->metadata->claim_type}\n";
    echo "Code: {$comment->metadata->code_snippet}\n";
    echo "Comment: {$comment->body}\n\n";
}
```

## Integration with AI Code Review Workflows

This feature is designed to support AI-powered code review verification pipelines:

1. **Fetch AI reviewer comments** with specific filtering
2. **Extract structured metadata** for analysis
3. **Identify high-priority issues** automatically
4. **Generate verification tests** based on claims
5. **Track review comment resolution**

Perfect for tools like Conduit that need to process and analyze code review feedback systematically.