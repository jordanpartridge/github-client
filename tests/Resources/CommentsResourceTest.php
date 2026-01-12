<?php

use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\CommentsResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);

    $this->mockCommentData = [
        'id' => 1,
        'node_id' => 'abc123',
        'path' => 'src/test.php',
        'position' => 5,
        'original_position' => 5,
        'commit_id' => 'abc123def456',
        'original_commit_id' => 'abc123def456',
        'user' => $this->createMockUserData('commenter', 1),
        'body' => 'Test comment body',
        'html_url' => 'https://github.com/owner/repo/pull/1#discussion_r1',
        'pull_request_url' => 'https://api.github.com/repos/owner/repo/pulls/1',
        'created_at' => '2024-01-01T00:00:00Z',
        'updated_at' => '2024-01-01T00:00:00Z',
    ];
});

describe('CommentsResource', function () {
    it('can access comments resource through Github facade', function () {
        $resource = Github::comments();

        expect($resource)->toBeInstanceOf(CommentsResource::class);
    });

    describe('forPullRequest method', function () {
        it('requires repository to be specified in filters', function () {
            expect(fn () => Github::comments()->forPullRequest(42))
                ->toThrow(InvalidArgumentException::class, 'Repository must be specified');
        });

        it('can fetch comments with repository filter', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->forPullRequest(42, [
                'repository' => 'owner/repo',
            ]);

            expect($comments)
                ->toBeArray()
                ->toHaveCount(1);
        });
    });

    describe('byAuthor method', function () {
        it('can filter comments by author', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->byAuthor(42, 'commenter', [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('byAuthorType method', function () {
        it('can filter comments by bot author type', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->byAuthorType(42, 'bot', [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });

        it('can filter comments by human author type', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->byAuthorType(42, 'human', [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('bySeverity method', function () {
        it('can filter comments by high severity', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->bySeverity(42, 'high', [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });

        it('can filter comments by medium severity', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->bySeverity(42, 'medium', [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });

        it('can filter comments by low severity', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->bySeverity(42, 'low', [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('forFile method', function () {
        it('can filter comments by file path', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->forFile(42, 'src/test.php', [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('codeRabbit method', function () {
        it('fetches CodeRabbit AI comments', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->codeRabbit(42, [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('bots method', function () {
        it('fetches all bot comments', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->bots(42, [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('humans method', function () {
        it('fetches all human comments', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->humans(42, [
                'repository' => 'owner/repo',
            ]);

            expect($comments)->toBeArray();
        });
    });

    describe('filter chaining', function () {
        it('can combine multiple filter options', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->mockCommentData], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::comments()->forPullRequest(42, [
                'repository' => 'owner/repo',
                'author' => 'coderabbitai',
                'severity' => 'high',
                'file_path' => 'src/test.php',
            ]);

            expect($comments)->toBeArray();
        });
    });
});
