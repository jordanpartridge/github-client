<?php

use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Issues\Sort;
use JordanPartridge\GithubClient\Enums\Issues\State;
use JordanPartridge\GithubClient\Facades\Github;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);
});

describe('Issue listing', function () {
    it('can list user issues across all repositories', function () {
        $mockIssue = $this->createMockIssueData();

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$mockIssue], 200),
        ]));

        $response = Github::issues()->all();
        $issues = $response->dto();

        expect($issues)
            ->toBeArray()
            ->toHaveCount(1)
            ->and($issues[0]->title)->toBe('Test Issue')
            ->and($issues[0]->state)->toBe('open')
            ->and($issues[0]->number)->toBe(1);
    });

    it('can list issues for a specific repository', function () {
        $mockIssue = $this->createMockIssueData();

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$mockIssue], 200),
        ]));

        $response = Github::issues()->forRepo('test', 'repo');
        $issues = $response->dto();

        expect($issues)
            ->toBeArray()
            ->toHaveCount(1)
            ->and($issues[0]->title)->toBe('Test Issue');
    });

    it('can filter issues by state', function () {
        $mockIssue = $this->createMockIssueData(['state' => 'closed']);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$mockIssue], 200),
        ]));

        $response = Github::issues()->forRepo('test', 'repo', state: State::CLOSED);
        $issues = $response->dto();

        expect($issues[0]->state)->toBe('closed');
    });

    it('can filter issues by labels', function () {
        $mockIssue = $this->createMockIssueData([
            'labels' => [
                ['id' => 1, 'name' => 'bug', 'color' => 'ff0000', 'description' => 'Bug report', 'default' => false],
            ],
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$mockIssue], 200),
        ]));

        $response = Github::issues()->forRepo('test', 'repo', labels: 'bug');
        $issues = $response->dto();

        expect($issues[0]->labels)
            ->toHaveCount(1)
            ->and($issues[0]->labels[0]->name)->toBe('bug');
    });

    it('can sort issues by created date', function () {
        $olderIssue = $this->createMockIssueData([
            'id' => 1,
            'number' => 1,
            'title' => 'Older Issue',
            'created_at' => '2024-01-01T00:00:00Z',
        ]);

        $newerIssue = $this->createMockIssueData([
            'id' => 2,
            'number' => 2,
            'title' => 'Newer Issue',
            'created_at' => '2024-01-02T00:00:00Z',
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$olderIssue, $newerIssue], 200),
        ]));

        $response = Github::issues()->forRepo(
            'test',
            'repo',
            sort: Sort::CREATED,
            direction: Direction::ASC
        );

        $issues = $response->dto();

        expect($issues)
            ->toHaveCount(2)
            ->and($issues[0]->title)->toBe('Older Issue')
            ->and($issues[1]->title)->toBe('Newer Issue');
    });

    it('filters out pull requests from issue listings', function () {
        $issue = $this->createMockIssueData();
        $pullRequest = $this->createMockIssueData([
            'id' => 2,
            'number' => 2,
            'title' => 'Test PR',
            'pull_request' => ['url' => 'https://api.github.com/repos/test/repo/pulls/2'],
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$issue, $pullRequest], 200),
        ]));

        $response = Github::issues()->forRepo('test', 'repo');
        $issues = $response->dto();

        expect($issues)
            ->toHaveCount(1)
            ->and($issues[0]->title)->toBe('Test Issue');
    });
});

describe('Issue auto-pagination', function () {
    it('fetches all issues across multiple pages', function () {
        $page1Response = MockResponse::make([
            $this->createMockIssueData(['id' => 1, 'number' => 1, 'title' => 'Issue 1']),
            $this->createMockIssueData(['id' => 2, 'number' => 2, 'title' => 'Issue 2']),
        ], 200, [
            'Link' => '</repos/test/repo/issues?page=2>; rel="next", </repos/test/repo/issues?page=2>; rel="last"',
        ]);

        $page2Response = MockResponse::make([
            $this->createMockIssueData(['id' => 3, 'number' => 3, 'title' => 'Issue 3']),
        ], 200, [
            'Link' => '</repos/test/repo/issues?page=1>; rel="first", </repos/test/repo/issues?page=1>; rel="prev"',
        ]);

        $mockClient = new MockClient;
        $mockClient->addResponse($page1Response);
        $mockClient->addResponse($page2Response);

        Github::connector()->withMockClient($mockClient);

        $allIssues = Github::issues()->allForRepo('test', 'repo');

        expect($allIssues)
            ->toBeArray()
            ->toHaveCount(3)
            ->and($allIssues[0]->title)->toBe('Issue 1')
            ->and($allIssues[1]->title)->toBe('Issue 2')
            ->and($allIssues[2]->title)->toBe('Issue 3');
    });

    it('throws exception when maximum page limit is exceeded', function () {
        $infiniteResponse = MockResponse::make([
            $this->createMockIssueData(),
        ], 200, [
            'Link' => '</repos/test/repo/issues?page=2>; rel="next"',
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => $infiniteResponse,
        ]));

        expect(fn () => Github::issues()->allForRepo('test', 'repo'))
            ->toThrow(RuntimeException::class, 'Maximum page limit (1000) exceeded during pagination');
    });
});

describe('Individual issue operations', function () {
    it('can get a specific issue', function () {
        $mockIssue = $this->createMockIssueData();

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($mockIssue, 200),
        ]));

        $issue = Github::issues()->get('test', 'repo', 1);

        expect($issue->title)->toBe('Test Issue')
            ->and($issue->number)->toBe(1)
            ->and($issue->state)->toBe('open');
    });

    it('can create a new issue', function () {
        $mockIssue = $this->createMockIssueData([
            'title' => 'New Issue',
            'body' => 'Issue description',
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($mockIssue, 201),
        ]));

        $issue = Github::issues()->create(
            owner: 'test',
            repo: 'repo',
            title: 'New Issue',
            body: 'Issue description'
        );

        expect($issue->title)->toBe('New Issue')
            ->and($issue->body)->toBe('Issue description');
    });

    it('can update an existing issue', function () {
        $mockIssue = $this->createMockIssueData([
            'title' => 'Updated Issue',
            'state' => 'closed',
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($mockIssue, 200),
        ]));

        $issue = Github::issues()->update(
            owner: 'test',
            repo: 'repo',
            issue_number: 1,
            title: 'Updated Issue',
            state: State::CLOSED
        );

        expect($issue->title)->toBe('Updated Issue')
            ->and($issue->state)->toBe('closed');
    });

    it('can close an issue', function () {
        $mockIssue = $this->createMockIssueData(['state' => 'closed']);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($mockIssue, 200),
        ]));

        $issue = Github::issues()->close('test', 'repo', 1);

        expect($issue->state)->toBe('closed');
    });

    it('can reopen an issue', function () {
        $mockIssue = $this->createMockIssueData(['state' => 'open']);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($mockIssue, 200),
        ]));

        $issue = Github::issues()->reopen('test', 'repo', 1);

        expect($issue->state)->toBe('open');
    });
});

describe('Issue comments', function () {
    it('can list comments for an issue', function () {
        $mockComment = $this->createMockCommentData();

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$mockComment], 200),
        ]));

        $comments = Github::issues()->comments('test', 'repo', 1);

        expect($comments)
            ->toBeArray()
            ->toHaveCount(1)
            ->and($comments[0]->body)->toBe('Test comment');
    });

    it('can add a comment to an issue', function () {
        $mockComment = $this->createMockCommentData(['body' => 'New comment']);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($mockComment, 201),
        ]));

        $comment = Github::issues()->addComment('test', 'repo', 1, 'New comment');

        expect($comment->body)->toBe('New comment');
    });
});

describe('Parameter validation', function () {
    it('throws error for invalid per_page value', function () {
        expect(fn () => Github::issues()->forRepo('test', 'repo', per_page: 101))
            ->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 100');
    });

    it('accepts valid enum parameters', function () {
        $mockIssue = $this->createMockIssueData();

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$mockIssue], 200),
        ]));

        $response = Github::issues()->forRepo(
            'test',
            'repo',
            state: State::OPEN,
            sort: Sort::CREATED,
            direction: Direction::DESC
        );

        expect($response->status())->toBe(200);
    });
});
