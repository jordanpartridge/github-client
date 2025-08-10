<?php

use JordanPartridge\GithubClient\Data\Repos\SearchRepositoriesData;
use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Requests\Repos\Search;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->mockClient = new MockClient;
    Github::connector()->withMockClient($this->mockClient);
});

describe('Search functionality', function () {
    it('can search repositories with basic query', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'total_count' => 2,
            'incomplete_results' => false,
            'items' => [
                [
                    'id' => 1,
                    'node_id' => 'MDEwOlJlcG9zaXRvcnkx',
                    'name' => 'test-repo',
                    'full_name' => 'test/test-repo',
                    'private' => false,
                    'owner' => [
                        'login' => 'test',
                        'id' => 1,
                        'node_id' => 'MDQ6VXNlcjE=',
                        'avatar_url' => 'https://github.com/images/error/test_happy.gif',
                        'gravatar_id' => '',
                        'url' => 'https://api.github.com/users/test',
                        'html_url' => 'https://github.com/test',
                        'followers_url' => 'https://api.github.com/users/test/followers',
                        'following_url' => 'https://api.github.com/users/test/following{/other_user}',
                        'gists_url' => 'https://api.github.com/users/test/gists{/gist_id}',
                        'starred_url' => 'https://api.github.com/users/test/starred{/owner}{/repo}',
                        'subscriptions_url' => 'https://api.github.com/users/test/subscriptions',
                        'organizations_url' => 'https://api.github.com/users/test/orgs',
                        'repos_url' => 'https://api.github.com/users/test/repos',
                        'events_url' => 'https://api.github.com/users/test/events{/privacy}',
                        'received_events_url' => 'https://api.github.com/users/test/received_events',
                        'type' => 'User',
                        'user_view_type' => 'public',
                        'site_admin' => false,
                    ],
                    'html_url' => 'https://github.com/test/test-repo',
                    'description' => 'Test repository',
                    'fork' => false,
                    'url' => 'https://api.github.com/repos/test/test-repo',
                    'forks_url' => 'https://api.github.com/repos/test/test-repo/forks',
                    'keys_url' => 'https://api.github.com/repos/test/test-repo/keys{/key_id}',
                    'collaborators_url' => 'https://api.github.com/repos/test/test-repo/collaborators{/collaborator}',
                    'teams_url' => 'https://api.github.com/repos/test/test-repo/teams',
                    'hooks_url' => 'https://api.github.com/repos/test/test-repo/hooks',
                    'issue_events_url' => 'https://api.github.com/repos/test/test-repo/issues/events{/number}',
                    'events_url' => 'https://api.github.com/repos/test/test-repo/events',
                    'assignees_url' => 'https://api.github.com/repos/test/test-repo/assignees{/user}',
                    'branches_url' => 'https://api.github.com/repos/test/test-repo/branches{/branch}',
                    'tags_url' => 'https://api.github.com/repos/test/test-repo/tags',
                    'blobs_url' => 'https://api.github.com/repos/test/test-repo/git/blobs{/sha}',
                    'git_tags_url' => 'https://api.github.com/repos/test/test-repo/git/tags{/sha}',
                    'git_refs_url' => 'https://api.github.com/repos/test/test-repo/git/refs{/sha}',
                    'trees_url' => 'https://api.github.com/repos/test/test-repo/git/trees{/sha}',
                    'statuses_url' => 'https://api.github.com/repos/test/test-repo/statuses/{sha}',
                    'languages_url' => 'https://api.github.com/repos/test/test-repo/languages',
                    'stargazers_url' => 'https://api.github.com/repos/test/test-repo/stargazers',
                    'contributors_url' => 'https://api.github.com/repos/test/test-repo/contributors',
                    'subscribers_url' => 'https://api.github.com/repos/test/test-repo/subscribers',
                    'subscription_url' => 'https://api.github.com/repos/test/test-repo/subscription',
                    'commits_url' => 'https://api.github.com/repos/test/test-repo/commits{/sha}',
                    'git_commits_url' => 'https://api.github.com/repos/test/test-repo/git/commits{/sha}',
                    'comments_url' => 'https://api.github.com/repos/test/test-repo/comments{/number}',
                    'issue_comment_url' => 'https://api.github.com/repos/test/test-repo/issues/comments{/number}',
                    'contents_url' => 'https://api.github.com/repos/test/test-repo/contents/{+path}',
                    'compare_url' => 'https://api.github.com/repos/test/test-repo/compare/{base}...{head}',
                    'merges_url' => 'https://api.github.com/repos/test/test-repo/merges',
                    'archive_url' => 'https://api.github.com/repos/test/test-repo/{archive_format}{/ref}',
                    'downloads_url' => 'https://api.github.com/repos/test/test-repo/downloads',
                    'issues_url' => 'https://api.github.com/repos/test/test-repo/issues{/number}',
                    'pulls_url' => 'https://api.github.com/repos/test/test-repo/pulls{/number}',
                    'milestones_url' => 'https://api.github.com/repos/test/test-repo/milestones{/number}',
                    'notifications_url' => 'https://api.github.com/repos/test/test-repo/notifications{?since,all,participating}',
                    'labels_url' => 'https://api.github.com/repos/test/test-repo/labels{/name}',
                    'releases_url' => 'https://api.github.com/repos/test/test-repo/releases{/id}',
                    'deployments_url' => 'https://api.github.com/repos/test/test-repo/deployments',
                    'created_at' => '2024-01-01T00:00:00Z',
                    'updated_at' => '2024-01-01T00:00:00Z',
                    'pushed_at' => '2024-01-01T00:00:00Z',
                    'git_url' => 'git://github.com/test/test-repo.git',
                    'ssh_url' => 'git@github.com:test/test-repo.git',
                    'clone_url' => 'https://github.com/test/test-repo.git',
                    'svn_url' => 'https://github.com/test/test-repo',
                    'homepage' => 'https://github.com',
                    'size' => 108,
                    'stargazers_count' => 80,
                    'watchers_count' => 9,
                    'language' => 'PHP',
                    'has_issues' => true,
                    'has_projects' => true,
                    'has_downloads' => true,
                    'has_wiki' => true,
                    'has_pages' => false,
                    'has_discussions' => false,
                    'forks_count' => 9,
                    'mirror_url' => null,
                    'archived' => false,
                    'disabled' => false,
                    'open_issues_count' => 0,
                    'license' => [
                        'key' => 'mit',
                        'name' => 'MIT License',
                        'spdx_id' => 'MIT',
                        'url' => 'https://api.github.com/licenses/mit',
                        'node_id' => 'MDc6TGljZW5zZW1pdA==',
                    ],
                    'allow_forking' => true,
                    'is_template' => false,
                    'web_commit_signoff_required' => false,
                    'topics' => ['conduit-component', 'laravel'],
                    'visibility' => 'public',
                    'forks' => 9,
                    'open_issues' => 0,
                    'watchers' => 9,
                    'default_branch' => 'main',
                    'permissions' => [
                        'admin' => false,
                        'maintain' => false,
                        'push' => false,
                        'triage' => false,
                        'pull' => true,
                    ],
                ],
            ],
        ], 200));

        $result = Github::repos()->search('topic:conduit-component');

        expect($result)->toBeInstanceOf(SearchRepositoriesData::class);
        expect($result->total_count)->toBe(2);
        expect($result->incomplete_results)->toBeFalse();
        expect($result->items)->toHaveCount(1);
        expect($result->items[0]->name)->toBe('test-repo');
        expect($result->items[0]->full_name)->toBe('test/test-repo');
    });

    it('can search repositories with sorting and pagination', function () {
        $this->mockClient->addResponse(MockResponse::make([
            'total_count' => 100,
            'incomplete_results' => false,
            'items' => [],
        ], 200));

        $result = Github::repos()->search(
            query: 'laravel',
            sort: 'stars',
            order: Direction::DESC,
            per_page: 10,
            page: 2,
        );

        expect($result)->toBeInstanceOf(SearchRepositoriesData::class);
        expect($result->total_count)->toBe(100);
        expect($result->incomplete_results)->toBeFalse();
        expect($result->items)->toBeArray();
    });

    it('validates search request parameters', function () {
        expect(function () {
            new Search('test', 'stars', Direction::ASC, 150);
        })->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        expect(function () {
            new Search('test', 'invalid-sort');
        })->toThrow(InvalidArgumentException::class, 'Sort must be one of: stars, forks, help-wanted-issues, updated');
    });

    it('constructs correct API endpoint', function () {
        $search = new Search('test-query');
        expect($search->resolveEndpoint())->toBe('/search/repositories');
    });

    it('constructs correct query parameters', function () {
        $search = new Search(
            searchQuery: 'topic:conduit-component',
            sort: 'stars',
            order: Direction::DESC,
            per_page: 20,
            page: 1,
        );

        $reflector = new ReflectionClass($search);
        $method = $reflector->getMethod('defaultQuery');
        $method->setAccessible(true);
        $query = $method->invoke($search);

        expect($query)->toBe([
            'q' => 'topic:conduit-component',
            'sort' => 'stars',
            'order' => 'desc',
            'per_page' => 20,
            'page' => 1,
        ]);
    });
});
