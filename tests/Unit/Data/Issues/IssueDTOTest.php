<?php

use JordanPartridge\GithubClient\Data\GitUserData;
use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
use JordanPartridge\GithubClient\Data\Issues\LabelDTO;

beforeEach(function () {
    $this->sampleData = [
        'id' => 12345,
        'number' => 42,
        'title' => 'Bug: Login not working',
        'body' => 'When I try to login, it fails.',
        'state' => 'open',
        'assignee' => $this->createMockUserData('assignee', 2),
        'assignees' => [
            $this->createMockUserData('assignee1', 2),
            $this->createMockUserData('assignee2', 3),
        ],
        'labels' => [
            [
                'id' => 1,
                'name' => 'bug',
                'color' => 'ff0000',
                'description' => 'Something is broken',
                'default' => false,
            ],
            [
                'id' => 2,
                'name' => 'priority-high',
                'color' => 'ff6600',
                'description' => 'High priority',
                'default' => false,
            ],
        ],
        'comments' => 5,
        'html_url' => 'https://github.com/owner/repo/issues/42',
        'user' => $this->createMockUserData('reporter', 1),
        'created_at' => '2024-01-15T10:30:00Z',
        'updated_at' => '2024-01-16T14:00:00Z',
        'closed_at' => null,
    ];
});

it('can create IssueDTO from API response', function () {
    $issue = IssueDTO::fromApiResponse($this->sampleData);

    expect($issue->id)->toBe(12345);
    expect($issue->number)->toBe(42);
    expect($issue->title)->toBe('Bug: Login not working');
    expect($issue->body)->toBe('When I try to login, it fails.');
    expect($issue->state)->toBe('open');
    expect($issue->assignee)->toBeInstanceOf(GitUserData::class);
    expect($issue->assignee->login)->toBe('assignee');
    expect($issue->assignees)->toHaveCount(2);
    expect($issue->assignees[0])->toBeInstanceOf(GitUserData::class);
    expect($issue->labels)->toHaveCount(2);
    expect($issue->labels[0])->toBeInstanceOf(LabelDTO::class);
    expect($issue->labels[0]->name)->toBe('bug');
    expect($issue->comments)->toBe(5);
    expect($issue->html_url)->toBe('https://github.com/owner/repo/issues/42');
    expect($issue->user)->toBeInstanceOf(GitUserData::class);
    expect($issue->user->login)->toBe('reporter');
    expect($issue->created_at)->toBe('2024-01-15T10:30:00Z');
    expect($issue->updated_at)->toBe('2024-01-16T14:00:00Z');
    expect($issue->closed_at)->toBeNull();
});

it('can convert IssueDTO to array', function () {
    $issue = IssueDTO::fromApiResponse($this->sampleData);
    $array = $issue->toArray();

    expect($array['id'])->toBe(12345);
    expect($array['number'])->toBe(42);
    expect($array['title'])->toBe('Bug: Login not working');
    expect($array['body'])->toBe('When I try to login, it fails.');
    expect($array['state'])->toBe('open');
    expect($array['assignee'])->toBeArray();
    expect($array['assignee']['login'])->toBe('assignee');
    expect($array['assignees'])->toHaveCount(2);
    expect($array['labels'])->toHaveCount(2);
    expect($array['labels'][0]['name'])->toBe('bug');
    expect($array['comments'])->toBe(5);
    expect($array['user']['login'])->toBe('reporter');
});

it('throws exception for pull request data', function () {
    $prData = array_merge($this->sampleData, [
        'pull_request' => [
            'url' => 'https://api.github.com/repos/owner/repo/pulls/42',
        ],
    ]);

    IssueDTO::fromApiResponse($prData);
})->throws(InvalidArgumentException::class, 'This is a pull request, not an issue');

it('handles null assignee', function () {
    $dataWithNullAssignee = $this->sampleData;
    $dataWithNullAssignee['assignee'] = null;

    $issue = IssueDTO::fromApiResponse($dataWithNullAssignee);

    expect($issue->assignee)->toBeNull();
});

it('handles empty body', function () {
    $dataWithEmptyBody = $this->sampleData;
    unset($dataWithEmptyBody['body']);

    $issue = IssueDTO::fromApiResponse($dataWithEmptyBody);

    expect($issue->body)->toBe('');
});

it('handles closed issue with closed_at date', function () {
    $closedIssue = $this->sampleData;
    $closedIssue['state'] = 'closed';
    $closedIssue['closed_at'] = '2024-01-17T09:00:00Z';

    $issue = IssueDTO::fromApiResponse($closedIssue);

    expect($issue->state)->toBe('closed');
    expect($issue->closed_at)->toBe('2024-01-17T09:00:00Z');
});

it('handles empty labels and assignees', function () {
    $dataWithEmpty = $this->sampleData;
    $dataWithEmpty['labels'] = [];
    $dataWithEmpty['assignees'] = [];

    $issue = IssueDTO::fromApiResponse($dataWithEmpty);

    expect($issue->labels)->toBe([]);
    expect($issue->assignees)->toBe([]);
});
