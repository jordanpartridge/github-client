# Conduit CLI Comment Management Integration

## Overview
This document outlines how the new github-client CRUD operations enable comprehensive comment management in Conduit CLI, supporting Issues #91, #92, and #93.

## Completed API Implementation

### ✅ Issue/PR General Comments (Issue #91)
- `IssuesResource::getComment($owner, $repo, $commentId): IssueCommentDTO`
- `IssuesResource::updateComment($owner, $repo, $commentId, $body): IssueCommentDTO`  
- `IssuesResource::deleteComment($owner, $repo, $commentId): bool`

### ✅ PR Review Comments (Issue #92)
- `PullRequestResource::getComment($owner, $repo, $commentId): PullRequestCommentDTO`
- `PullRequestResource::updateComment($owner, $repo, $commentId, $body): PullRequestCommentDTO`
- `PullRequestResource::deleteComment($owner, $repo, $commentId): bool`

### ✅ Advanced Comment Filtering (Already Available)
- `CommentsResource::forPullRequest($prNumber, $filters): array`
- `CommentsResource::byAuthor($prNumber, $author): array`
- `CommentsResource::bySeverity($prNumber, $severity): array`

## Proposed Conduit CLI Commands (Issue #93)

### 1. General Comment Management

```bash
# List comments (leverages existing functionality)
conduit comments:list {issue_or_pr} [--repo=] [--type=issue|review|all]

# Get specific comment
conduit comments:show {comment_id} [--repo=] [--type=issue|review]

# Edit comments with external editor integration
conduit comments:edit {comment_id} [--repo=] [--body=] [--editor] [--type=issue|review]

# Delete comments with confirmation
conduit comments:delete {comment_id} [--repo=] [--confirm] [--type=issue|review]

# Reply to comments (create new in thread)
conduit comments:reply {comment_id} [--repo=] [--body=] [--editor]
```

### 2. Review-Specific Commands

```bash
# Edit review comments (inline code comments)
conduit prs:comments:edit {comment_id} [--repo=] [--body=] [--editor]

# Delete review comments
conduit prs:comments:delete {comment_id} [--repo=] [--confirm]

# Resolve/unresolve conversations (if GitHub API supports)
conduit prs:comments:resolve {comment_id} [--repo=]
```

### 3. Bulk Operations

```bash
# Bulk delete resolved comments
conduit comments:cleanup {pr} [--repo=] [--resolved-only] [--dry-run]

# Export comment history
conduit comments:export {issue_or_pr} [--repo=] [--format=json|csv|md]
```

## Implementation Examples

### Basic Comment CRUD

```php
// Conduit CLI would use the github-client like this:

// Get a comment
$comment = Github::issues()->getComment('owner', 'repo', 12345);

// Edit a comment
$updated = Github::issues()->updateComment('owner', 'repo', 12345, 'Updated comment text');

// Delete a comment
$success = Github::issues()->deleteComment('owner', 'repo', 12345);
```

### PR Review Comment Management

```php
// Get PR review comment
$reviewComment = Github::pullRequests()->getComment('owner', 'repo', 67890);

// Update PR review comment
$updated = Github::pullRequests()->updateComment('owner', 'repo', 67890, 'Updated review feedback');

// Delete PR review comment
$success = Github::pullRequests()->deleteComment('owner', 'repo', 67890);
```

### Advanced Filtering for Bulk Operations

```php
// Get all CodeRabbit comments that can be cleaned up
$aiComments = Github::comments()->forPullRequest(42, [
    'repository' => 'owner/repo',
    'author' => 'coderabbitai',
    'severity' => 'low'
]);

// Bulk delete old resolved comments
foreach ($aiComments as $comment) {
    if ($comment->isResolved()) {
        Github::pullRequests()->deleteComment('owner', 'repo', $comment->id);
    }
}
```

## Command Integration Patterns

### 1. Unified Comment Detection

```php
// Conduit CLI logic to determine comment type
function getCommentType(int $commentId, string $repo): string 
{
    try {
        // Try as PR review comment first
        Github::pullRequests()->getComment(..., $commentId);
        return 'review';
    } catch (NotFound $e) {
        // Fall back to issue comment
        Github::issues()->getComment(..., $commentId);
        return 'issue';
    }
}
```

### 2. Editor Integration

```bash
# Conduit CLI can open comments in user's preferred editor
conduit comments:edit 12345 --editor

# This would:
# 1. Fetch comment with Github::issues()->getComment()
# 2. Open in $EDITOR with current content
# 3. Update with Github::issues()->updateComment() on save
```

### 3. Repository Detection

```php
// Conduit CLI uses existing DetectsRepository trait
class CommentsEditCommand extends Command 
{
    use DetectsRepository;
    
    public function handle()
    {
        $repo = $this->detectRepository();
        $comment = Github::issues()->getComment($repo->owner, $repo->name, $this->commentId);
        // ... rest of edit logic
    }
}
```

## Benefits for Conduit Users

### 🔄 Complete Comment Lifecycle
- **Create**: Existing functionality
- **Read**: Enhanced with single comment fetching  
- **Update**: New CRUD operations
- **Delete**: New CRUD operations

### 🎯 Developer Workflow Integration
- Edit comments in preferred editor (vim, nano, code, etc.)
- Bulk operations for comment cleanup
- Scriptable comment management for CI/CD

### 🤖 Code Review Management  
- Resolve discussions programmatically
- Clean up outdated AI feedback
- Export comment history for analysis

### 🚀 Cross-Platform Consistency
- Unified interface for GitHub comment operations
- Works with both issues and PR comments seamlessly
- Consistent patterns across all comment types

## Technical Implementation Notes

### Error Handling
```php
// Robust error handling for missing comments
try {
    $comment = Github::issues()->getComment('owner', 'repo', 999999);
} catch (ApiException $e) {
    if ($e->getCode() === 404) {
        throw new CommentNotFoundException("Comment 999999 not found");
    }
    throw $e;
}
```

### Validation
- All new request classes validate comment IDs (must be positive integers)
- Comment body validation prevents empty comments
- Proper error messages for debugging

### Type Safety
- Strongly typed return values (`IssueCommentDTO`, `PullRequestCommentDTO`, `bool`)
- IDE autocompletion and error detection
- Consistent API patterns across all operations

## Conclusion

The github-client now provides complete CRUD operations for both issue comments and PR review comments, enabling Conduit CLI to offer comprehensive comment management functionality. The API is designed for:

- **Consistency**: Same patterns for all comment types
- **Type Safety**: Full PHP type-hinting and validation  
- **Developer Experience**: Intuitive method names and clear documentation
- **Error Handling**: Robust validation and meaningful error messages

This foundation enables Conduit CLI to become the most comprehensive GitHub comment management tool available.