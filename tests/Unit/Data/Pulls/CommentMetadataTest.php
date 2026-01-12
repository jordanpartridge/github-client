<?php

use JordanPartridge\GithubClient\Data\Pulls\CommentMetadata;

it('can create CommentMetadata with all fields', function () {
    $metadata = new CommentMetadata(
        severity: 'high',
        file_path: 'src/Example.php',
        line_number: 42,
        code_snippet: 'echo $unsafe;',
        claim_type: 'security',
        reviewer_type: 'human',
        raw_patterns: ['bold' => ['Important']],
    );

    expect($metadata->severity)->toBe('high');
    expect($metadata->file_path)->toBe('src/Example.php');
    expect($metadata->line_number)->toBe(42);
    expect($metadata->code_snippet)->toBe('echo $unsafe;');
    expect($metadata->claim_type)->toBe('security');
    expect($metadata->reviewer_type)->toBe('human');
    expect($metadata->raw_patterns)->toBe(['bold' => ['Important']]);
});

it('can convert CommentMetadata to array', function () {
    $metadata = new CommentMetadata(
        severity: 'medium',
        file_path: 'test.php',
        line_number: 10,
    );

    $array = $metadata->toArray();

    expect($array['severity'])->toBe('medium');
    expect($array['file_path'])->toBe('test.php');
    expect($array['line_number'])->toBe(10);
    expect($array['code_snippet'])->toBeNull();
    expect($array['claim_type'])->toBeNull();
    expect($array['reviewer_type'])->toBeNull();
    expect($array['raw_patterns'])->toBe([]);
});

it('extracts high severity from explicit marker', function () {
    $metadata = CommentMetadata::extract('[SEVERITY: HIGH] Critical bug found!', 'file.php', 10, 'reviewer');

    expect($metadata->severity)->toBe('high');
});

it('extracts medium severity from warning keyword', function () {
    $metadata = CommentMetadata::extract('Warning: This could cause issues.', 'file.php', 10, 'reviewer');

    expect($metadata->severity)->toBe('medium');
});

it('extracts low severity from suggestion keyword', function () {
    $metadata = CommentMetadata::extract('Suggestion: Use a different approach.', 'file.php', 10, 'reviewer');

    expect($metadata->severity)->toBe('low');
});

it('extracts severity from emoji', function () {
    $highMetadata = CommentMetadata::extract('This is bad', 'file.php', 10, 'reviewer');
    $mediumMetadata = CommentMetadata::extract('Be careful here ⚠️', 'file.php', 10, 'reviewer');
    $lowMetadata = CommentMetadata::extract('This is fine ✅', 'file.php', 10, 'reviewer');

    expect($mediumMetadata->severity)->toBe('medium');
    expect($lowMetadata->severity)->toBe('low');
});

it('extracts line number from position', function () {
    $metadata = CommentMetadata::extract('Comment body', 'file.php', 25, 'reviewer');

    expect($metadata->line_number)->toBe(25);
});

it('extracts line number from comment text', function () {
    $metadata = CommentMetadata::extract('Check line 42 for the issue', 'file.php', null, 'reviewer');

    expect($metadata->line_number)->toBe(42);
});

it('extracts code snippet from code block', function () {
    $body = "Here's the fix:\n```php\necho 'fixed';\n```";
    $metadata = CommentMetadata::extract($body, 'file.php', 10, 'reviewer');

    expect($metadata->code_snippet)->toBe("echo 'fixed';");
});

it('extracts code snippet from inline code', function () {
    $body = 'Change `$variable` to `$newVariable`';
    $metadata = CommentMetadata::extract($body, 'file.php', 10, 'reviewer');

    expect($metadata->code_snippet)->toBe('$variable');
});

it('detects sql injection claim type', function () {
    $metadata = CommentMetadata::extract('This is vulnerable to SQL injection!', 'file.php', 10, 'reviewer');

    expect($metadata->claim_type)->toBe('sql_injection');
});

it('detects xss claim type', function () {
    $metadata = CommentMetadata::extract('This could lead to XSS attacks.', 'file.php', 10, 'reviewer');

    expect($metadata->claim_type)->toBe('xss');
});

it('detects performance claim type', function () {
    $metadata = CommentMetadata::extract('This has performance implications.', 'file.php', 10, 'reviewer');

    expect($metadata->claim_type)->toBe('performance');
});

it('detects unused code claim type', function () {
    $metadata = CommentMetadata::extract('This unused variable should be removed.', 'file.php', 10, 'reviewer');

    expect($metadata->claim_type)->toBe('unused_code');
});

it('determines human reviewer type', function () {
    $metadata = CommentMetadata::extract('Nice work!', 'file.php', 10, 'johndoe');

    expect($metadata->reviewer_type)->toBe('human');
});

it('determines coderabbit reviewer type', function () {
    $metadata = CommentMetadata::extract('Nice work!', 'file.php', 10, 'coderabbitai[bot]');

    expect($metadata->reviewer_type)->toBe('coderabbit');
});

it('determines dependabot reviewer type', function () {
    $metadata = CommentMetadata::extract('Bumping version', 'file.php', 10, 'dependabot[bot]');

    expect($metadata->reviewer_type)->toBe('dependabot');
});

it('determines github actions reviewer type', function () {
    $metadata = CommentMetadata::extract('CI result', 'file.php', 10, 'github-actions[bot]');

    expect($metadata->reviewer_type)->toBe('github_actions');
});

it('determines bot reviewer type for generic bot', function () {
    $metadata = CommentMetadata::extract('Automated comment', 'file.php', 10, 'mybot[bot]');

    expect($metadata->reviewer_type)->toBe('bot');
});

it('extracts raw patterns from markdown', function () {
    $body = '**Important**: Check this `code` and [link](http://example.com)';
    $metadata = CommentMetadata::extract($body, 'file.php', 10, 'reviewer');

    expect($metadata->raw_patterns)->toHaveKey('bold');
    expect($metadata->raw_patterns['bold'])->toContain('Important');
    expect($metadata->raw_patterns)->toHaveKey('links');
});

it('handles null author', function () {
    $metadata = CommentMetadata::extract('Comment', 'file.php', 10, null);

    expect($metadata->reviewer_type)->toBeNull();
});

it('handles body without patterns', function () {
    $metadata = CommentMetadata::extract('Simple comment without patterns', 'file.php', 10, 'reviewer');

    expect($metadata->severity)->toBeNull();
    expect($metadata->code_snippet)->toBeNull();
    expect($metadata->claim_type)->toBeNull();
});
