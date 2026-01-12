<?php

use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Issues\IssueCommentDTO;

beforeEach(function () {
    $this->sampleData = [
        'id' => 98765,
        'body' => 'This is a test comment on the issue.',
        'user' => $this->createMockUserData('commenter', 5),
        'html_url' => 'https://github.com/owner/repo/issues/42#issuecomment-98765',
        'created_at' => '2024-01-16T10:00:00Z',
        'updated_at' => '2024-01-16T10:30:00Z',
    ];
});

it('can create IssueCommentDTO from API response', function () {
    $comment = IssueCommentDTO::fromApiResponse($this->sampleData);

    expect($comment->id)->toBe(98765);
    expect($comment->body)->toBe('This is a test comment on the issue.');
    expect($comment->user)->toBeInstanceOf(GitUserData::class);
    expect($comment->user->login)->toBe('commenter');
    expect($comment->html_url)->toBe('https://github.com/owner/repo/issues/42#issuecomment-98765');
    expect($comment->created_at)->toBe('2024-01-16T10:00:00Z');
    expect($comment->updated_at)->toBe('2024-01-16T10:30:00Z');
});

it('can convert IssueCommentDTO to array', function () {
    $comment = IssueCommentDTO::fromApiResponse($this->sampleData);
    $array = $comment->toArray();

    expect($array['id'])->toBe(98765);
    expect($array['body'])->toBe('This is a test comment on the issue.');
    expect($array['user'])->toBeArray();
    expect($array['user']['login'])->toBe('commenter');
    expect($array['html_url'])->toBe('https://github.com/owner/repo/issues/42#issuecomment-98765');
    expect($array['created_at'])->toBe('2024-01-16T10:00:00Z');
    expect($array['updated_at'])->toBe('2024-01-16T10:30:00Z');
});

it('handles markdown in comment body', function () {
    $markdownData = $this->sampleData;
    $markdownData['body'] = "## Header\n\n```php\necho 'hello';\n```\n\n- List item";

    $comment = IssueCommentDTO::fromApiResponse($markdownData);

    expect($comment->body)->toContain('## Header');
    expect($comment->body)->toContain('```php');
});

it('handles emoji in comment body', function () {
    $emojiData = $this->sampleData;
    $emojiData['body'] = 'Great work! :+1: :rocket:';

    $comment = IssueCommentDTO::fromApiResponse($emojiData);

    expect($comment->body)->toBe('Great work! :+1: :rocket:');
});
