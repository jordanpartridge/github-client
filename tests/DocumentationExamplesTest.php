<?php

use ShadeTheory\GithubClient\Data\Issues\IssueDTO;
use ShadeTheory\GithubClient\Data\Pulls\PullRequestDTO;
use ShadeTheory\GithubClient\Data\Repos\RepoData;
use ShadeTheory\GithubClient\Enums\Direction;
use ShadeTheory\GithubClient\Enums\Issues\State;
use ShadeTheory\GithubClient\Enums\MergeMethod;
use ShadeTheory\GithubClient\Enums\Sort;
use ShadeTheory\GithubClient\Enums\Visibility;
use ShadeTheory\GithubClient\Facades\Github;
use ShadeTheory\GithubClient\ValueObjects\Repo;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);
});

describe('README examples', function () {
    it('lists repositories and loads one by full name', function () {
        $payload = $this->createMockRepoData('github-client', 1, 'shadetheory');

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$payload], 200),
        ]));

        $repos = Github::repos()->all()->dto();

        expect($repos)->toBeArray()
            ->and($repos[0])->toBeInstanceOf(RepoData::class);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($payload, 200),
        ]));

        $repo = Github::getRepo('shadetheory/github-client');

        expect($repo->name)->toBe('github-client')
            ->and($repo->owner->login)->toBe('shadetheory')
            ->and($repo->stargazers_count)->toBe(80);

        $same = Github::repos()->get(Repo::fromFullName('shadetheory/github-client'));

        expect($same->full_name)->toBe('shadetheory/github-client');
    });

    it('paginates public repositories with enums', function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([
                $this->createMockRepoData('github-client', 1, 'shadetheory'),
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
            'html_url' => 'https://github.com/shadetheory/github-client/issues/42',
            'labels' => [
                ['id' => 1, 'name' => 'bug', 'color' => 'd73a4a', 'description' => null, 'default' => true],
            ],
        ]);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($created, 201),
        ]));

        $issue = Github::issues()->create(
            owner: 'shadetheory',
            repo: 'github-client',
            title: 'Bug: Something broke',
            body: 'Here are the details...',
            labels: ['bug'],
            assignees: ['shadetheory'],
        );

        expect($issue)->toBeInstanceOf(IssueDTO::class)
            ->and($issue->number)->toBe(42)
            ->and($issue->html_url)->toBe('https://github.com/shadetheory/github-client/issues/42');

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$created], 200),
        ]));

        $bugs = Github::issues()->forRepo(
            owner: 'shadetheory',
            repo: 'github-client',
            state: State::OPEN,
            labels: 'bug',
        )->dto();

        expect($bugs)->toHaveCount(1)
            ->and($bugs[0]->number)->toBe(42);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make([$this->createMockCommentData()], 200),
        ]));

        $comments = Github::issues()->comments('shadetheory', 'github-client', $issue->number);

        expect($comments)->toHaveCount(1);

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($this->createMockCommentData(['body' => 'Fixed in the latest release.']), 201),
        ]));

        $comment = Github::issues()->addComment(
            'shadetheory',
            'github-client',
            $issue->number,
            'Fixed in the latest release.',
        );

        expect($comment->body)->toBe('Fixed in the latest release.');

        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($this->createMockIssueData(['number' => 42, 'state' => 'closed']), 200),
        ]));

        $closed = Github::issues()->close('shadetheory', 'github-client', $issue->number);

        expect($closed->state)->toBe('closed');
    });

    it('creates and merges a pull request', function () {
        Github::connector()->withMockClient(new MockClient([
            '*' => MockResponse::make($this->createMockPullRequestData(['number' => 8]), 201),
        ]));

        $pr = Github::pullRequests()->create(
            owner: 'shadetheory',
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
            owner: 'shadetheory',
            repo: 'github-client',
            number: $pr->number,
            mergeMethod: MergeMethod::Squash,
        );

        expect($merged)->toBeTrue();
    });

    it('reads changed files from the commit response', function () {
        $sha = '0123456789abcdef0123456789abcdef01234567';
        $files = [
            ['filename' => 'src/Github.php', 'status' => 'modified', 'additions' => 7, 'deletions' => 2],
            ['filename' => 'README.md', 'status' => 'added', 'additions' => 15, 'deletions' => 0],
        ];
        Github::connector()->withMockClient(new MockClient([
            "https://api.github.com/repos/owner/repo/commits/{$sha}" => MockResponse::make([
                'sha' => $sha,
                'files' => $files,
            ]),
        ]));

        $response = Github::files()->all('owner/repo', $sha);

        expect($response->json('files'))->toBe($files);
    });
});
