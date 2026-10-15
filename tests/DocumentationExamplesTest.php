<?php

use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use JordanPartridge\GithubClient\Data\Pulls\PullRequestDTO;
use JordanPartridge\GithubClient\Data\Repos\RepoData;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Issues\State;
use JordanPartridge\GithubClient\Enums\MergeMethod;
use JordanPartridge\GithubClient\Enums\Sort;
use JordanPartridge\GithubClient\Enums\Visibility;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);
});

describe('README examples', function () {
    it('lists repositories and loads one by full name', function () {
        $payload = $this->createMockRepoData('github-client', 1, 'jordanpartridge');

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$payload], 200),
        ]));

        $repos = Github::repos()->all()->dto();

        expect($repos)->toBeArray()
            ->and($repos[0])->toBeInstanceOf(RepoData::class);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($payload, 200),
        ]));

        $repo = Github::getRepo('jordanpartridge/github-client');

        expect($repo->name)->toBe('github-client')
            ->and($repo->owner->login)->toBe('jordanpartridge')
            ->and($repo->stargazers_count)->toBe(80);

        $same = Github::repos()->get(Repo::fromFullName('jordanpartridge/github-client'));

        expect($same->full_name)->toBe('jordanpartridge/github-client');
    });

    it('paginates public repositories with enums', function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([
                $this->createMockRepoData('github-client', 1, 'jordanpartridge'),
            ], 200),
        ]));

        $repos = Github::repos()->allWithPagination(
            visibility: Visibility::PUBLIC,
            sort: Sort::CREATED,
            direction: Direction::DESC,
        );

        expect($repos)->toHaveCount(1)
            ->and($repos[0]->name)->toBe('github-client');
    });

    it('creates an issue, lists bugs, comments, and closes it', function () {
        $created = $this->createMockIssueData([
            'number' => 42,
            'title' => 'Bug: Something broke',
            'html_url' => 'https://github.com/jordanpartridge/github-client/issues/42',
            'labels' => [
                ['id' => 1, 'name' => 'bug', 'color' => 'd73a4a', 'description' => null, 'default' => true],
            ],
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($created, 201),
        ]));

        $issue = Github::issues()->create(
            owner: 'jordanpartridge',
            repo: 'github-client',
            title: 'Bug: Something broke',
            body: 'Here are the details...',
            labels: ['bug'],
            assignees: ['jordanpartridge'],
        );

        expect($issue)->toBeInstanceOf(IssueDTO::class)
            ->and($issue->number)->toBe(42)
            ->and($issue->html_url)->toBe('https://github.com/jordanpartridge/github-client/issues/42');

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$created], 200),
        ]));

        $bugs = Github::issues()->forRepo(
            owner: 'jordanpartridge',
            repo: 'github-client',
            state: State::OPEN,
            labels: 'bug',
        )->dto();

        expect($bugs)->toHaveCount(1)
            ->and($bugs[0]->number)->toBe(42);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$this->createMockCommentData()], 200),
        ]));

        $comments = Github::issues()->comments('jordanpartridge', 'github-client', $issue->number);

        expect($comments)->toHaveCount(1);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($this->createMockCommentData(['body' => 'Fixed in the latest release.']), 201),
        ]));

        $comment = Github::issues()->addComment(
            'jordanpartridge',
            'github-client',
            $issue->number,
            'Fixed in the latest release.',
        );

        expect($comment->body)->toBe('Fixed in the latest release.');

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($this->createMockIssueData(['number' => 42, 'state' => 'closed']), 200),
        ]));

        $closed = Github::issues()->close('jordanpartridge', 'github-client', $issue->number);

        expect($closed->state)->toBe('closed');
    });

    it('creates and merges a pull request', function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($this->createMockPullRequestData(['number' => 8]), 201),
        ]));

        $pr = Github::pullRequests()->create(
            owner: 'jordanpartridge',
            repo: 'github-client',
            title: 'Add a feature',
            head: 'feature-branch',
            base: 'main',
            body: 'This pull request adds the feature.',
            draft: false,
        );

        expect($pr)->toBeInstanceOf(PullRequestDTO::class)
            ->and($pr->number)->toBe(8);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([
                'merged' => true,
                'sha' => '0123456789abcdef0123456789abcdef01234567',
                'message' => 'Pull request successfully merged',
            ], 200),
        ]));

        $merged = Github::pullRequests()->merge(
            owner: 'jordanpartridge',
            repo: 'github-client',
            number: $pr->number,
            mergeMethod: MergeMethod::Squash,
        );

        expect($merged)->toBeTrue();
    });
});
