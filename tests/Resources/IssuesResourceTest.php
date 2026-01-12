<?php

use JordanPartridge\GithubClient\Data\Issues\IssueCommentDTO;
use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Issues\Sort;
use JordanPartridge\GithubClient\Enums\Issues\State;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\IssuesResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);
});

describe('IssuesResource', function () {
    it('can access issues resource through Github facade', function () {
        $resource = Github::issues();

        expect($resource)->toBeInstanceOf(IssuesResource::class);
    });

    describe('getComment method', function () {
        it('can get a single comment by ID', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'id' => 123,
                    'body' => 'Test comment',
                    'user' => $this->createMockUserData('commenter', 1),
                    'html_url' => 'https://github.com/test/repo/issues/1#issuecomment-123',
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-01T00:00:00Z',
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comment = Github::issues()->getComment('test', 'repo', 123);

            expect($comment)
                ->toBeInstanceOf(IssueCommentDTO::class)
                ->and($comment->id)->toBe(123)
                ->and($comment->body)->toBe('Test comment');
        });
    });

    describe('updateComment method', function () {
        it('can update a comment', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'id' => 123,
                    'body' => 'Updated comment',
                    'user' => $this->createMockUserData('commenter', 1),
                    'html_url' => 'https://github.com/test/repo/issues/1#issuecomment-123',
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-02T00:00:00Z',
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comment = Github::issues()->updateComment('test', 'repo', 123, 'Updated comment');

            expect($comment)
                ->toBeInstanceOf(IssueCommentDTO::class)
                ->and($comment->body)->toBe('Updated comment');
        });
    });

    describe('deleteComment method', function () {
        it('returns true on successful deletion', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 204),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::issues()->deleteComment('test', 'repo', 123);

            expect($result)->toBeTrue();
        });

        it('returns false on failed deletion', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['message' => 'Not found'], 404),
            ]);

            Github::connector()->withMockClient($mockClient);

            $result = Github::issues()->deleteComment('test', 'repo', 999);

            expect($result)->toBeFalse();
        });
    });

    describe('comments method with pagination', function () {
        it('accepts pagination parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    $this->createMockCommentData(['id' => 1]),
                    $this->createMockCommentData(['id' => 2]),
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::issues()->comments(
                owner: 'test',
                repo: 'repo',
                issue_number: 1,
                per_page: 50,
                page: 2,
            );

            expect($comments)->toBeArray()->toHaveCount(2);
        });

        it('accepts since parameter', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockCommentData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $comments = Github::issues()->comments(
                owner: 'test',
                repo: 'repo',
                issue_number: 1,
                since: '2024-01-01T00:00:00Z',
            );

            expect($comments)->toBeArray();
        });
    });

    describe('all method with filters', function () {
        it('accepts all filter parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::issues()->all(
                per_page: 50,
                page: 1,
                state: State::OPEN,
                labels: 'bug,enhancement',
                sort: Sort::CREATED,
                direction: Direction::DESC,
                assignee: 'testuser',
                creator: 'testuser',
                mentioned: 'testuser',
                since: '2024-01-01T00:00:00Z',
            );

            expect($response->status())->toBe(200);
        });
    });

    describe('forRepo method with filters', function () {
        it('accepts all filter parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::issues()->forRepo(
                owner: 'test',
                repo: 'repo',
                per_page: 50,
                page: 1,
                state: State::CLOSED,
                labels: 'bug',
                sort: Sort::UPDATED,
                direction: Direction::ASC,
                assignee: 'testuser',
                creator: 'testuser',
                mentioned: 'testuser',
                since: '2024-01-01T00:00:00Z',
            );

            expect($response->status())->toBe(200);
        });
    });

    describe('allForRepo method with filters', function () {
        it('accepts all filter parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $issues = Github::issues()->allForRepo(
                owner: 'test',
                repo: 'repo',
                per_page: 50,
                state: State::ALL,
                labels: 'bug',
                sort: Sort::COMMENTS,
                direction: Direction::DESC,
                assignee: 'testuser',
                creator: 'testuser',
                mentioned: 'testuser',
                since: '2024-01-01T00:00:00Z',
            );

            expect($issues)->toBeArray();
        });
    });

    describe('create method with all parameters', function () {
        it('can create issue with all optional parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make($this->createMockIssueData([
                    'title' => 'New Issue',
                    'body' => 'Issue body',
                    'assignees' => [$this->createMockUserData('testuser', 1)],
                    'labels' => [['id' => 1, 'name' => 'bug', 'color' => 'ff0000', 'description' => 'Bug report', 'default' => false]],
                ]), 201),
            ]);

            Github::connector()->withMockClient($mockClient);

            $issue = Github::issues()->create(
                owner: 'test',
                repo: 'repo',
                title: 'New Issue',
                body: 'Issue body',
                assignees: ['testuser'],
                milestone: 1,
                labels: ['bug'],
            );

            expect($issue)
                ->toBeInstanceOf(IssueDTO::class)
                ->and($issue->title)->toBe('New Issue');
        });
    });

    describe('update method with all parameters', function () {
        it('can update issue with all optional parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make($this->createMockIssueData([
                    'title' => 'Updated Title',
                    'body' => 'Updated body',
                    'state' => 'closed',
                ]), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $issue = Github::issues()->update(
                owner: 'test',
                repo: 'repo',
                issue_number: 1,
                title: 'Updated Title',
                body: 'Updated body',
                state: State::CLOSED,
                assignees: ['testuser'],
                milestone: 2,
                labels: ['enhancement'],
            );

            expect($issue)
                ->toBeInstanceOf(IssueDTO::class)
                ->and($issue->title)->toBe('Updated Title')
                ->and($issue->state)->toBe('closed');
        });
    });

    describe('close and reopen methods', function () {
        it('close method updates state to closed', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make($this->createMockIssueData(['state' => 'closed']), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $issue = Github::issues()->close('test', 'repo', 1);

            expect($issue->state)->toBe('closed');
        });

        it('reopen method updates state to open', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make($this->createMockIssueData(['state' => 'open']), 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $issue = Github::issues()->reopen('test', 'repo', 1);

            expect($issue->state)->toBe('open');
        });
    });

    describe('Sort enum usage', function () {
        it('can sort by CREATED', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::issues()->forRepo('test', 'repo', sort: Sort::CREATED);

            expect($response->status())->toBe(200);
        });

        it('can sort by UPDATED', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::issues()->forRepo('test', 'repo', sort: Sort::UPDATED);

            expect($response->status())->toBe(200);
        });

        it('can sort by COMMENTS', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::issues()->forRepo('test', 'repo', sort: Sort::COMMENTS);

            expect($response->status())->toBe(200);
        });
    });

    describe('State enum usage', function () {
        it('can filter by OPEN state', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData(['state' => 'open'])], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::issues()->forRepo('test', 'repo', state: State::OPEN);

            expect($response->status())->toBe(200);
        });

        it('can filter by CLOSED state', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData(['state' => 'closed'])], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::issues()->forRepo('test', 'repo', state: State::CLOSED);

            expect($response->status())->toBe(200);
        });

        it('can filter by ALL state', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([$this->createMockIssueData()], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::issues()->forRepo('test', 'repo', state: State::ALL);

            expect($response->status())->toBe(200);
        });
    });
});
