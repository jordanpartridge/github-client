# DTO Migration Guide: Summary vs Detail DTOs

## Overview

In v2.8.0, we've introduced a new DTO pattern that **solves Issue #73** and provides better type safety for GitHub API responses. This is a **non-breaking** addition - your existing code continues to work exactly as before.

## The Problem We Solved

**Issue #73**: Comment counts always returned 0 from list endpoints because GitHub's API doesn't include detailed statistics in list responses.

```php
// Before: Misleading zeros from list endpoint
$prs = Github::pullRequests()->all('owner', 'repo');
echo $prs[0]->comments; // Always 0 😞 (misleading)

// After: Clear separation
$summaries = Github::pullRequests()->summaries('owner', 'repo'); // Fast, no comment counts
$detail = Github::pullRequests()->detail('owner', 'repo', 47);   // Accurate comment counts
echo $detail->comments; // Actual count! 🎉
```

## New DTO Types

### 1. `PullRequestSummaryDTO`
- **Used for**: List endpoint responses (`/repos/owner/repo/pulls`)
- **Contains**: Basic PR info (title, state, URLs, dates)
- **Does NOT contain**: Comment counts, code statistics
- **Benefits**: Fast, lightweight, no misleading zeros

### 2. `PullRequestDetailDTO`
- **Used for**: Individual endpoint responses (`/repos/owner/repo/pulls/123`)
- **Contains**: Complete PR data including comment counts and code stats
- **Extends**: `PullRequestSummaryDTO` (has all summary fields plus detailed ones)
- **Benefits**: Accurate data, rich utility methods

## Migration Paths

### Option 1: Keep Using Existing Methods (No Changes)

```php
// Your existing code still works exactly the same
$prs = Github::pullRequests()->all('owner', 'repo');
$pr = Github::pullRequests()->get('owner', 'repo', 123);

// Still returns PullRequestDTO objects as before
```

### Option 2: Adopt New Explicit Methods (Recommended)

```php
// NEW: Explicit lightweight listing
$summaries = Github::pullRequests()->summaries('owner', 'repo');
// Returns: PullRequestSummaryDTO[] - fast, no detailed fields

// NEW: Explicit detailed fetching  
$detail = Github::pullRequests()->detail('owner', 'repo', 123);
// Returns: PullRequestDetailDTO - complete data with accurate counts

// NEW: Optimized workflow for recent PRs with details
$recentWithDetails = Github::pullRequests()->recentDetails('owner', 'repo', 5);
// Returns: PullRequestDetailDTO[] - recent PRs with accurate comment counts
```

### Option 3: Use Smart Factory (Advanced)

```php
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTOFactory;

// Automatically detects response type and creates appropriate DTO
$dto = PullRequestDTOFactory::fromResponse($apiResponseData);

// Analyze what would be created
$analysis = PullRequestDTOFactory::analyzeResponse($apiResponseData);
```

## For Conduit Users

### Before (Issue #73)
```bash
# conduit prs showed: 💬0 📝0 (incorrect)
conduit prs --repo=conduit-ui/conduit
```

### After (Fixed!)
```php
// Update your conduit command to use:
$details = Github::pullRequests()->recentDetails('conduit-ui', 'conduit', 10);

foreach ($details as $pr) {
    echo "PR #{$pr->number}: 💬{$pr->comments} 📝{$pr->review_comments}"; // Accurate! ✅
}
```

## Backward Compatibility Promise

✅ **100% Backward Compatible**
- All existing methods work unchanged
- All existing DTOs (`PullRequestDTO`) continue to work
- No breaking changes whatsoever
- Opt-in enhancement

## When to Use Each Approach

### Use `summaries()` when:
- Displaying PR lists/tables
- Quick overviews without detailed stats
- Performance is critical
- You don't need comment counts

### Use `detail()` when:
- Showing individual PR pages
- Need accurate comment/review counts
- Displaying code change statistics
- Building detailed PR reports

### Use `recentDetails()` when:
- Building dashboards with comment counts
- Recent activity feeds with accurate stats
- Workflow prioritization based on activity

## New Features in Detail DTOs

```php
$detail = Github::pullRequests()->detail('owner', 'repo', 123);

// Rich utility methods
echo $detail->getTotalLinesChanged();    // additions + deletions
echo $detail->getAdditionRatio();        // percentage of additions
echo $detail->hasComments();             // bool: any comments?
echo $detail->getTotalComments();        // comments + review_comments

// Formatted summary for display
$summary = $detail->getSummary();
// Returns formatted data perfect for CLI/UI display
```

## Performance Considerations

### List Operations (Fast ⚡)
```php
// Single API call, lightweight response
$summaries = Github::pullRequests()->summaries('owner', 'repo');
```

### Detail Operations (Accurate but Slower 🎯)
```php
// Multiple API calls, rate limit aware
$details = Github::pullRequests()->recentDetails('owner', 'repo', 5); // Max 5 for safety
```

## Migration Timeline

- **v2.8.0**: New DTOs introduced, fully optional
- **v3.0.0**: May deprecate old methods with migration warnings
- **v4.0.0**: Potential breaking changes (far future)

## FAQs

**Q: Do I need to change my existing code?**
A: No! Your existing code continues to work exactly as before.

**Q: Will this affect performance?**
A: Only if you choose to use the new `detail()` methods, which make additional API calls for accuracy.

**Q: How do I fix Issue #73 in my app?**
A: Replace list operations that need comment counts with `recentDetails()` or individual `detail()` calls.

**Q: Can I mix old and new approaches?**
A: Yes! Use whatever works best for each use case.

## Example: Complete Migration

```php
// BEFORE: Potentially misleading data
class PullRequestController {
    public function index() {
        $prs = Github::pullRequests()->all('owner', 'repo');
        return view('prs.index', compact('prs'));
        // Template shows: 💬0 📝0 for all PRs (misleading)
    }
}

// AFTER: Accurate and performant
class PullRequestController {
    public function index() {
        // Fast listing without misleading zeros
        $summaries = Github::pullRequests()->summaries('owner', 'repo');
        return view('prs.index', compact('summaries'));
        // Template shows basic info, no comment counts
    }
    
    public function dashboard() {
        // Accurate data for dashboard with comment counts
        $recentWithDetails = Github::pullRequests()->recentDetails('owner', 'repo', 10);
        return view('prs.dashboard', compact('recentWithDetails'));
        // Template shows: 💬1 📝19 (accurate!)
    }
    
    public function show($number) {
        // Complete data for individual PR page
        $detail = Github::pullRequests()->detail('owner', 'repo', $number);
        return view('prs.show', compact('detail'));
    }
}
```

This pattern provides the best of both worlds: **fast listings** and **accurate detailed data** when you need it! 🚀