# GitHub Repository Maintainer Agent

An autonomous agent for managing GitHub pull requests, issues, and repository maintenance tasks. This agent is designed to work with the `github-client` Laravel package to provide automated repository management.

## Overview

This agent handles common repository maintenance workflows including:
- Reviewing and triaging issues
- Managing pull request lifecycles
- Enforcing contribution guidelines
- Automating release processes
- Monitoring repository health
- Handling dependency updates

---

## Skill 1: Issue Triage

Automatically categorize, label, and prioritize incoming issues based on content analysis.

### Workflow

1. Read the issue content and extract key information
2. Determine issue type (bug, feature, question, documentation)
3. Assign appropriate labels
4. Set priority based on severity indicators
5. Add initial triage comment

### Example

```php
use JordanPartridge\GithubClient\Facades\Github;

// Fetch new issues without labels
$issues = Github::issues()->all('owner/repo', state: 'open');

foreach ($issues as $issue) {
    // Skip already triaged issues
    if (!empty($issue->labels)) {
        continue;
    }

    $title = strtolower($issue->title);
    $body = strtolower($issue->body ?? '');

    // Determine issue type based on content
    $labels = [];

    if (str_contains($title, 'bug') || str_contains($body, 'error') || str_contains($body, 'crash')) {
        $labels[] = 'bug';
        $priority = 'high';
    } elseif (str_contains($title, 'feature') || str_contains($body, 'would be nice')) {
        $labels[] = 'enhancement';
        $priority = 'medium';
    } elseif (str_contains($title, '?') || str_contains($body, 'how do i')) {
        $labels[] = 'question';
        $priority = 'low';
    }

    // Apply labels via GitHub API
    Github::issues()->addLabels('owner/repo', $issue->number, $labels);

    // Post triage comment
    $comment = "Thanks for opening this issue! I've categorized this as: " . implode(', ', $labels);
    Github::issues()->createComment('owner/repo', $issue->number, $comment);
}
```

### CLI Commands

```bash
# List open issues needing triage
gh issue list --state open --label "" --json number,title,body

# Add labels to an issue
gh issue edit {NUMBER} --add-label "bug,high-priority"

# Add triage comment
gh issue comment {NUMBER} --body "Triaged as bug. Assigned high priority."
```

---

## Skill 2: Pull Request Review

Perform automated code review checks on incoming pull requests.

### Workflow

1. Fetch PR details and changed files
2. Run automated checks (tests, linting, security scans)
3. Analyze code changes for common issues
4. Post review comments with findings
5. Approve or request changes based on results

### Example

```php
use JordanPartridge\GithubClient\Facades\Github;

// Get open PRs ready for review
$pulls = Github::pulls()->all('owner/repo', state: 'open');

foreach ($pulls as $pr) {
    // Get the files changed in this PR
    $files = Github::pulls()->files('owner/repo', $pr->number);

    $issues = [];

    foreach ($files as $file) {
        // Check for common issues
        if (str_ends_with($file->filename, '.php')) {
            // Check for debugging statements left in code
            if (str_contains($file->patch ?? '', 'dd(') || str_contains($file->patch ?? '', 'dump(')) {
                $issues[] = "Found debugging statement in `{$file->filename}`";
            }

            // Check for console.log equivalents
            if (str_contains($file->patch ?? '', 'var_dump')) {
                $issues[] = "Found `var_dump` in `{$file->filename}` - use proper logging instead";
            }
        }

        // Check for large files
        if ($file->changes > 500) {
            $issues[] = "Large change ({$file->changes} lines) in `{$file->filename}` - consider splitting";
        }
    }

    // Post review
    if (empty($issues)) {
        Github::pulls()->createReview('owner/repo', $pr->number, [
            'event' => 'APPROVE',
            'body' => 'Automated checks passed. LGTM!'
        ]);
    } else {
        Github::pulls()->createReview('owner/repo', $pr->number, [
            'event' => 'REQUEST_CHANGES',
            'body' => "Found issues:\n- " . implode("\n- ", $issues)
        ]);
    }
}
```

### CLI Commands

```bash
# List PRs pending review
gh pr list --state open --json number,title,author,reviewDecision

# View PR diff
gh pr diff {NUMBER}

# Approve a PR
gh pr review {NUMBER} --approve --body "Automated checks passed."

# Request changes
gh pr review {NUMBER} --request-changes --body "Issues found: ..."

# Run tests for PR
gh pr checkout {NUMBER} && vendor/bin/pest
```

---

## Skill 3: Merge Conflict Resolution

Detect and help resolve merge conflicts in pull requests.

### Workflow

1. Identify PRs with merge conflicts
2. Analyze conflicting files
3. Attempt automatic resolution for simple conflicts
4. Post detailed guidance for complex conflicts
5. Update PR status

### Example

```php
use JordanPartridge\GithubClient\Facades\Github;

// Find PRs with merge conflicts
$pulls = Github::pulls()->all('owner/repo', state: 'open');

foreach ($pulls as $pr) {
    if (!$pr->mergeable) {
        // Get PR branch info
        $headBranch = $pr->head->ref;
        $baseBranch = $pr->base->ref;

        // Post helpful comment with resolution steps
        $comment = <<<COMMENT
        This PR has merge conflicts that need to be resolved.

        **To resolve locally:**
        ```bash
        # Fetch latest changes
        git fetch origin

        # Checkout the PR branch
        git checkout {$headBranch}

        # Merge or rebase from base branch
        git merge origin/{$baseBranch}
        # OR
        git rebase origin/{$baseBranch}

        # Resolve conflicts in your editor, then:
        git add .
        git commit -m "Resolve merge conflicts with {$baseBranch}"
        git push origin {$headBranch}
        ```

        Need help? Tag a maintainer for assistance.
        COMMENT;

        Github::issues()->createComment('owner/repo', $pr->number, $comment);
    }
}
```

### CLI Commands

```bash
# Check if PR is mergeable
gh pr view {NUMBER} --json mergeable,mergeStateStatus

# Checkout PR locally
gh pr checkout {NUMBER}

# Get conflict status
git status --short | grep "^UU"

# After resolving conflicts
git add .
git commit -m "Resolve merge conflicts"
git push

# Force push if rebased (with lease for safety)
git push --force-with-lease
```

---

## Skill 4: Release Management

Automate the release process including changelog generation and version tagging.

### Workflow

1. Collect commits since last release
2. Categorize changes (features, fixes, breaking changes)
3. Generate changelog content
4. Create release with notes
5. Notify stakeholders

### Example

```php
use JordanPartridge\GithubClient\Facades\Github;

// Get the latest release
$releases = Github::releases()->all('owner/repo');
$latestRelease = $releases[0] ?? null;
$lastTag = $latestRelease?->tag_name ?? 'v0.0.0';

// Get commits since last release
$commits = Github::commits()->all('owner/repo', since: $latestRelease?->created_at);

// Categorize commits by conventional commit prefixes
$features = [];
$fixes = [];
$breaking = [];
$other = [];

foreach ($commits as $commit) {
    $message = $commit->message;

    if (str_starts_with($message, 'feat:') || str_starts_with($message, 'feat(')) {
        $features[] = $message;
    } elseif (str_starts_with($message, 'fix:') || str_starts_with($message, 'fix(')) {
        $fixes[] = $message;
    } elseif (str_contains($message, 'BREAKING CHANGE')) {
        $breaking[] = $message;
    } else {
        $other[] = $message;
    }
}

// Determine next version (semantic versioning)
$version = ltrim($lastTag, 'v');
[$major, $minor, $patch] = explode('.', $version);

if (!empty($breaking)) {
    $major++;
    $minor = 0;
    $patch = 0;
} elseif (!empty($features)) {
    $minor++;
    $patch = 0;
} else {
    $patch++;
}

$newVersion = "v{$major}.{$minor}.{$patch}";

// Generate changelog
$changelog = "## What's Changed\n\n";
if (!empty($features)) {
    $changelog .= "### New Features\n";
    foreach ($features as $f) {
        $changelog .= "- {$f}\n";
    }
}
if (!empty($fixes)) {
    $changelog .= "\n### Bug Fixes\n";
    foreach ($fixes as $f) {
        $changelog .= "- {$f}\n";
    }
}
if (!empty($breaking)) {
    $changelog .= "\n### Breaking Changes\n";
    foreach ($breaking as $b) {
        $changelog .= "- {$b}\n";
    }
}

// Create the release
Github::releases()->create('owner/repo', [
    'tag_name' => $newVersion,
    'name' => $newVersion,
    'body' => $changelog,
    'draft' => false,
    'prerelease' => false,
]);
```

### CLI Commands

```bash
# List recent releases
gh release list --limit 5

# View commits since last release
git log $(git describe --tags --abbrev=0)..HEAD --oneline

# Create a new release
gh release create v1.2.0 --title "v1.2.0" --notes "Changelog content here"

# Create release from auto-generated notes
gh release create v1.2.0 --generate-notes

# Edit an existing release
gh release edit v1.2.0 --notes "Updated notes"
```

---

## Skill 5: Stale Issue Management

Identify and manage stale issues and pull requests to keep the repository clean.

### Workflow

1. Find issues/PRs with no activity for defined period
2. Add warning label and comment
3. Close if no response after grace period
4. Generate stale item reports

### Example

```php
use JordanPartridge\GithubClient\Facades\Github;
use Carbon\Carbon;

$staleDays = 30;
$closeDays = 7;
$staleDate = Carbon::now()->subDays($staleDays);
$closeDate = Carbon::now()->subDays($staleDays + $closeDays);

// Get all open issues
$issues = Github::issues()->all('owner/repo', state: 'open');

foreach ($issues as $issue) {
    $updatedAt = Carbon::parse($issue->updated_at);
    $labels = array_column($issue->labels, 'name');

    // Skip issues with "pinned" or "help wanted" labels
    if (array_intersect(['pinned', 'help wanted'], $labels)) {
        continue;
    }

    // Check if already marked stale and ready to close
    if (in_array('stale', $labels) && $updatedAt->lt($closeDate)) {
        Github::issues()->update('owner/repo', $issue->number, [
            'state' => 'closed'
        ]);
        Github::issues()->createComment('owner/repo', $issue->number,
            "This issue has been automatically closed due to inactivity. " .
            "Feel free to reopen if this is still relevant."
        );
        continue;
    }

    // Mark as stale if no recent activity
    if ($updatedAt->lt($staleDate) && !in_array('stale', $labels)) {
        Github::issues()->addLabels('owner/repo', $issue->number, ['stale']);
        Github::issues()->createComment('owner/repo', $issue->number,
            "This issue has been automatically marked as stale because it has not had " .
            "recent activity. It will be closed in {$closeDays} days if no further " .
            "activity occurs. Thank you for your contributions!"
        );
    }
}
```

### CLI Commands

```bash
# Find issues not updated in 30 days
gh issue list --state open --json number,title,updatedAt | \
  jq '.[] | select(.updatedAt < (now - 2592000 | todate))'

# Add stale label
gh issue edit {NUMBER} --add-label "stale"

# Close stale issue with comment
gh issue close {NUMBER} --comment "Closed due to inactivity."

# Reopen if needed
gh issue reopen {NUMBER}

# Bulk operations with xargs
gh issue list --label stale --json number -q '.[].number' | \
  xargs -I {} gh issue close {} --comment "Closing stale issue"
```

---

## Skill 6: Dependency Security Monitoring

Monitor and respond to dependency security vulnerabilities.

### Workflow

1. Check for security advisories affecting dependencies
2. Create issues for new vulnerabilities
3. Suggest or auto-create PRs for updates
4. Track remediation progress

### Example

```php
use JordanPartridge\GithubClient\Facades\Github;
use Illuminate\Support\Facades\Process;

// Run composer audit to check for vulnerabilities
$audit = Process::run('composer audit --format=json');
$vulnerabilities = json_decode($audit->output(), true);

if (!empty($vulnerabilities['advisories'])) {
    foreach ($vulnerabilities['advisories'] as $package => $advisories) {
        foreach ($advisories as $advisory) {
            // Check if issue already exists
            $existingIssues = Github::issues()->all('owner/repo',
                state: 'open',
                labels: 'security'
            );

            $alreadyReported = collect($existingIssues)->contains(function ($issue) use ($advisory) {
                return str_contains($issue->title, $advisory['cve'] ?? $advisory['advisoryId']);
            });

            if ($alreadyReported) {
                continue;
            }

            // Create security issue
            $title = "Security: {$package} - " . ($advisory['cve'] ?? $advisory['advisoryId']);
            $body = <<<BODY
            ## Security Vulnerability Detected

            **Package:** {$package}
            **Advisory:** {$advisory['advisoryId']}
            **CVE:** {$advisory['cve']}
            **Severity:** {$advisory['severity']}

            ### Description
            {$advisory['title']}

            ### Affected Versions
            {$advisory['affectedVersions']}

            ### Recommended Action
            Update to version {$advisory['reportedAt']} or later.

            ```bash
            composer update {$package}
            ```

            ### References
            - {$advisory['link']}
            BODY;

            Github::issues()->create('owner/repo', [
                'title' => $title,
                'body' => $body,
                'labels' => ['security', 'dependencies', 'high-priority']
            ]);
        }
    }
}

// Also check for outdated packages
$outdated = Process::run('composer outdated --format=json');
$packages = json_decode($outdated->output(), true);

// Create tracking issue for major updates
$majorUpdates = array_filter($packages['installed'] ?? [], function ($pkg) {
    return version_compare($pkg['version'], $pkg['latest'], '<')
        && explode('.', $pkg['version'])[0] !== explode('.', $pkg['latest'])[0];
});

if (!empty($majorUpdates)) {
    $updateList = "";
    foreach ($majorUpdates as $pkg) {
        $updateList .= "- [ ] `{$pkg['name']}`: {$pkg['version']} -> {$pkg['latest']}\n";
    }

    Github::issues()->create('owner/repo', [
        'title' => 'Dependency Updates: Major Version Upgrades Available',
        'body' => "## Major dependency updates available\n\n{$updateList}",
        'labels' => ['dependencies', 'maintenance']
    ]);
}
```

### CLI Commands

```bash
# Run composer security audit
composer audit

# Check for outdated packages
composer outdated

# Update a specific package
composer update vendor/package

# View Dependabot alerts (if enabled)
gh api repos/{owner}/{repo}/dependabot/alerts --jq '.[] | {package: .dependency.package.name, severity: .security_advisory.severity}'

# Enable Dependabot via API
gh api repos/{owner}/{repo}/vulnerability-alerts -X PUT

# Create security issue
gh issue create --title "Security: package-name CVE-2024-XXXXX" \
  --body "Security vulnerability detected..." \
  --label "security,high-priority"
```

---

## Agent Configuration

### Environment Variables

```bash
# Required
GITHUB_TOKEN=your_github_token

# Optional
GITHUB_REPO=owner/repo          # Default repository
STALE_DAYS=30                   # Days before marking stale
CLOSE_DAYS=7                    # Grace period before closing
```

### Scheduled Tasks

Configure these in Laravel's scheduler (`app/Console/Kernel.php`):

```php
protected function schedule(Schedule $schedule)
{
    // Daily triage of new issues
    $schedule->command('github:triage-issues')->dailyAt('09:00');

    // Hourly PR review checks
    $schedule->command('github:review-prs')->hourly();

    // Weekly stale issue cleanup
    $schedule->command('github:cleanup-stale')->weekly();

    // Daily security scan
    $schedule->command('github:security-scan')->dailyAt('06:00');
}
```

---

## Error Handling

All skills should implement proper error handling:

```php
use JordanPartridge\GithubClient\Exceptions\RateLimitException;
use JordanPartridge\GithubClient\Exceptions\AuthenticationException;
use JordanPartridge\GithubClient\Exceptions\ApiException;

try {
    $result = Github::issues()->all('owner/repo');
} catch (RateLimitException $e) {
    // Wait and retry, or queue for later
    $resetTime = $e->getResetTime();
    Log::warning("Rate limited. Resets at: {$resetTime}");
} catch (AuthenticationException $e) {
    // Token expired or invalid
    Log::error("Authentication failed: {$e->getMessage()}");
} catch (ApiException $e) {
    // General API error
    Log::error("API error: {$e->getMessage()}");
}
```

---

## Integration with CI/CD

Example GitHub Actions workflow:

```yaml
name: Repository Maintenance

on:
  schedule:
    - cron: '0 9 * * *'  # Daily at 9am UTC
  workflow_dispatch:

jobs:
  maintain:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install dependencies
        run: composer install

      - name: Run maintenance tasks
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        run: |
          php artisan github:triage-issues
          php artisan github:cleanup-stale
          php artisan github:security-scan
```
