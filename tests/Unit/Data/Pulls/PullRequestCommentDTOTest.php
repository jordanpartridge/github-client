<?php

use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Pulls\CommentMetadata;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;

beforeEach(function () {
    $this->sampleData = [
        'id' => 123456,
        'node_id' => 'PRR_comment123',
        'path' => 'src/Example.php',
        'position' => 10,
        'original_position' => 10,
        'commit_id' => 'abc123def456',
        'original_commit_id' => 'abc123def456',
        'user' => $this->createMockUserData('reviewer', 2),
        'body' => 'This looks good, but consider refactoring this method.',
        'html_url' => 'https://github.com/owner/repo/pull/42#discussion_r123456',
        'pull_request_url' => 'https://api.github.com/repos/owner/repo/pulls/42',
        'created_at' => '2024-01-16T10:00:00Z',
        'updated_at' => '2024-01-16T10:00:00Z',
    ];
});

it('can create PullRequestCommentDTO from array', function () {
    $comment = PullRequestCommentDTO::fromArray($this->sampleData);

    expect($comment->id)->toBe(123456);
    expect($comment->node_id)->toBe('PRR_comment123');
    expect($comment->path)->toBe('src/Example.php');
    expect($comment->position)->toBe(10);
    expect($comment->original_position)->toBe(10);
    expect($comment->commit_id)->toBe('abc123def456');
    expect($comment->original_commit_id)->toBe('abc123def456');
    expect($comment->user)->toBeInstanceOf(GitUserData::class);
    expect($comment->user->login)->toBe('reviewer');
    expect($comment->body)->toBe('This looks good, but consider refactoring this method.');
    expect($comment->html_url)->toBe('https://github.com/owner/repo/pull/42#discussion_r123456');
    expect($comment->pull_request_url)->toBe('https://api.github.com/repos/owner/repo/pulls/42');
    expect($comment->created_at)->toBe('2024-01-16T10:00:00Z');
    expect($comment->updated_at)->toBe('2024-01-16T10:00:00Z');
    expect($comment->metadata)->toBeInstanceOf(CommentMetadata::class);
});

it('can create PullRequestCommentDTO from API response', function () {
    $comment = PullRequestCommentDTO::fromApiResponse($this->sampleData);

    expect($comment->id)->toBe(123456);
    expect($comment->path)->toBe('src/Example.php');
});

it('can convert PullRequestCommentDTO to array', function () {
    $comment = PullRequestCommentDTO::fromArray($this->sampleData);
    $array = $comment->toArray();

    expect($array['id'])->toBe(123456);
    expect($array['node_id'])->toBe('PRR_comment123');
    expect($array['path'])->toBe('src/Example.php');
    expect($array['position'])->toBe(10);
    expect($array['user']['login'])->toBe('reviewer');
    expect($array['body'])->toBe('This looks good, but consider refactoring this method.');
    expect($array['metadata'])->toBeArray();
});

it('handles missing position with default', function () {
    $dataWithoutPosition = $this->sampleData;
    unset($dataWithoutPosition['position']);
    unset($dataWithoutPosition['original_position']);

    $comment = PullRequestCommentDTO::fromArray($dataWithoutPosition);

    expect($comment->position)->toBe(-1);
    expect($comment->original_position)->toBe(-1);
});

it('extracts metadata from comment with severity', function () {
    $dataWithSeverity = $this->sampleData;
    $dataWithSeverity['body'] = '[SEVERITY: HIGH] This is a critical security issue!';

    $comment = PullRequestCommentDTO::fromArray($dataWithSeverity);

    expect($comment->metadata)->toBeInstanceOf(CommentMetadata::class);
    expect($comment->metadata->severity)->toBe('high');
});

it('extracts metadata from bot reviewer', function () {
    $botData = $this->sampleData;
    $botData['user']['login'] = 'coderabbitai[bot]';

    $comment = PullRequestCommentDTO::fromArray($botData);

    expect($comment->metadata->reviewer_type)->toBe('coderabbit');
});
