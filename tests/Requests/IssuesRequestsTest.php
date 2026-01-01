<?php

use JordanPartridge\GithubClient\Enums\Direction;
use JordanPartridge\GithubClient\Enums\Issues\Sort;
use JordanPartridge\GithubClient\Enums\Issues\State;
use JordanPartridge\GithubClient\Requests\Issues\Comments;
use JordanPartridge\GithubClient\Requests\Issues\Create;
use JordanPartridge\GithubClient\Requests\Issues\CreateComment;
use JordanPartridge\GithubClient\Requests\Issues\DeleteComment;
use JordanPartridge\GithubClient\Requests\Issues\Get;
use JordanPartridge\GithubClient\Requests\Issues\GetComment;
use JordanPartridge\GithubClient\Requests\Issues\Index;
use JordanPartridge\GithubClient\Requests\Issues\RepoIndex;
use JordanPartridge\GithubClient\Requests\Issues\Update;
use JordanPartridge\GithubClient\Requests\Issues\UpdateComment;
use Saloon\Enums\Method;

describe('Issues Requests', function () {
    describe('Issues\Index', function () {
        it('constructs with default parameters', function () {
            $request = new Index();

            expect($request->resolveEndpoint())->toBe('/user/issues');
        });

        it('uses GET method', function () {
            $request = new Index();

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts all optional parameters', function () {
            $request = new Index(
                per_page: 50,
                page: 2,
                state: State::OPEN,
                labels: 'bug,enhancement',
                sort: Sort::CREATED,
                direction: Direction::DESC,
                assignee: 'testuser',
                creator: 'creator',
                mentioned: 'mentioned',
                since: '2024-01-01T00:00:00Z',
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe([
                'per_page' => 50,
                'page' => 2,
                'state' => 'open',
                'labels' => 'bug,enhancement',
                'sort' => 'created',
                'direction' => 'desc',
                'assignee' => 'testuser',
                'creator' => 'creator',
                'mentioned' => 'mentioned',
                'since' => '2024-01-01T00:00:00Z',
            ]);
        });

        it('filters null values from query parameters', function () {
            $request = new Index(per_page: 30, state: State::CLOSED);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 30, 'state' => 'closed']);
        });

        it('throws exception for per_page less than 1', function () {
            new Index(per_page: 0);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('throws exception for per_page greater than 100', function () {
            new Index(per_page: 101);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');
    });

    describe('Issues\RepoIndex', function () {
        it('constructs with required parameters', function () {
            $request = new RepoIndex('owner', 'repo');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues');
        });

        it('uses GET method', function () {
            $request = new RepoIndex('owner', 'repo');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts all optional parameters', function () {
            $request = new RepoIndex(
                'owner',
                'repo',
                per_page: 50,
                page: 2,
                state: State::ALL,
                labels: 'bug',
                sort: Sort::UPDATED,
                direction: Direction::ASC,
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toHaveKey('per_page', 50);
            expect($query)->toHaveKey('state', 'all');
            expect($query)->toHaveKey('sort', 'updated');
            expect($query)->toHaveKey('direction', 'asc');
        });

        it('throws exception for per_page out of range', function () {
            new RepoIndex('owner', 'repo', per_page: 0);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');
    });

    describe('Issues\Get', function () {
        it('constructs with required parameters', function () {
            $request = new Get('owner', 'repo', 42);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/42');
        });

        it('uses GET method', function () {
            $request = new Get('owner', 'repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('throws exception for issue number less than 1', function () {
            new Get('owner', 'repo', 0);
        })->throws(InvalidArgumentException::class, 'Issue number must be a positive integer');

        it('throws exception for negative issue number', function () {
            new Get('owner', 'repo', -1);
        })->throws(InvalidArgumentException::class, 'Issue number must be a positive integer');

        it('has createDtoFromResponse method', function () {
            $request = new Get('owner', 'repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Issues\Create', function () {
        it('constructs with required parameters', function () {
            $request = new Create('owner', 'repo', 'Test Issue');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues');
        });

        it('uses POST method', function () {
            $request = new Create('owner', 'repo', 'Test Issue');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::POST);
        });

        it('includes all body parameters when provided', function () {
            $request = new Create(
                'owner',
                'repo',
                'Test Issue',
                bodyText: 'Issue body',
                assignees: ['user1', 'user2'],
                milestone: 1,
                labels: ['bug', 'priority'],
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe([
                'title' => 'Test Issue',
                'body' => 'Issue body',
                'assignees' => ['user1', 'user2'],
                'milestone' => 1,
                'labels' => ['bug', 'priority'],
            ]);
        });

        it('filters null values from body', function () {
            $request = new Create('owner', 'repo', 'Test Issue');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe(['title' => 'Test Issue']);
        });

        it('has createDtoFromResponse method', function () {
            $request = new Create('owner', 'repo', 'Test');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Issues\Update', function () {
        it('constructs with required parameters', function () {
            $request = new Update('owner', 'repo', 42);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/42');
        });

        it('uses PATCH method', function () {
            $request = new Update('owner', 'repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::PATCH);
        });

        it('includes all body parameters when provided', function () {
            $request = new Update(
                'owner',
                'repo',
                42,
                title: 'Updated Title',
                bodyText: 'Updated body',
                state: State::CLOSED,
                assignees: ['user1'],
                milestone: 2,
                labels: ['fixed'],
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe([
                'title' => 'Updated Title',
                'body' => 'Updated body',
                'state' => 'closed',
                'assignees' => ['user1'],
                'milestone' => 2,
                'labels' => ['fixed'],
            ]);
        });

        it('throws exception for issue number less than 1', function () {
            new Update('owner', 'repo', 0);
        })->throws(InvalidArgumentException::class, 'Issue number must be a positive integer');

        it('filters null values from body', function () {
            $request = new Update('owner', 'repo', 1, title: 'New Title');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe(['title' => 'New Title']);
        });
    });

    describe('Issues\Comments', function () {
        it('constructs with required parameters', function () {
            $request = new Comments('owner', 'repo', 42);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/42/comments');
        });

        it('uses GET method', function () {
            $request = new Comments('owner', 'repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts pagination and since parameters', function () {
            $request = new Comments('owner', 'repo', 42, per_page: 50, page: 2, since: '2024-01-01T00:00:00Z');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe([
                'per_page' => 50,
                'page' => 2,
                'since' => '2024-01-01T00:00:00Z',
            ]);
        });

        it('throws exception for issue number less than 1', function () {
            new Comments('owner', 'repo', 0);
        })->throws(InvalidArgumentException::class, 'Issue number must be a positive integer');

        it('throws exception for per_page out of range', function () {
            new Comments('owner', 'repo', 1, per_page: 101);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('has createDtoFromResponse method', function () {
            $request = new Comments('owner', 'repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Issues\CreateComment', function () {
        it('constructs with required parameters', function () {
            $request = new CreateComment('owner', 'repo', 42, 'Comment body');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/42/comments');
        });

        it('uses POST method', function () {
            $request = new CreateComment('owner', 'repo', 1, 'Body');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::POST);
        });

        it('includes body in request body', function () {
            $request = new CreateComment('owner', 'repo', 42, 'Comment body');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe(['body' => 'Comment body']);
        });

        it('throws exception for issue number less than 1', function () {
            new CreateComment('owner', 'repo', 0, 'Body');
        })->throws(InvalidArgumentException::class, 'Issue number must be a positive integer');

        it('throws exception for empty body', function () {
            $request = new CreateComment('owner', 'repo', 1, '   ');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $method->invoke($request);
        })->throws(InvalidArgumentException::class, 'Comment body cannot be empty');
    });

    describe('Issues\GetComment', function () {
        it('constructs with required parameters', function () {
            $request = new GetComment('owner', 'repo', 123);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/comments/123');
        });

        it('uses GET method', function () {
            $request = new GetComment('owner', 'repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('throws exception for comment ID less than 1', function () {
            new GetComment('owner', 'repo', 0);
        })->throws(InvalidArgumentException::class, 'Comment ID must be a positive integer');

        it('throws exception for negative comment ID', function () {
            new GetComment('owner', 'repo', -5);
        })->throws(InvalidArgumentException::class, 'Comment ID must be a positive integer');

        it('has createDtoFromResponse method', function () {
            $request = new GetComment('owner', 'repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Issues\UpdateComment', function () {
        it('constructs with required parameters', function () {
            $request = new UpdateComment('owner', 'repo', 123, 'Updated body');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/comments/123');
        });

        it('uses PATCH method', function () {
            $request = new UpdateComment('owner', 'repo', 1, 'Body');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::PATCH);
        });

        it('includes body in request body', function () {
            $request = new UpdateComment('owner', 'repo', 123, 'Updated body');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe(['body' => 'Updated body']);
        });

        it('throws exception for comment ID less than 1', function () {
            new UpdateComment('owner', 'repo', 0, 'Body');
        })->throws(InvalidArgumentException::class, 'Comment ID must be a positive integer');

        it('throws exception for empty body', function () {
            $request = new UpdateComment('owner', 'repo', 1, '');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $method->invoke($request);
        })->throws(InvalidArgumentException::class, 'Comment body cannot be empty');
    });

    describe('Issues\DeleteComment', function () {
        it('constructs with required parameters', function () {
            $request = new DeleteComment('owner', 'repo', 123);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/comments/123');
        });

        it('uses DELETE method', function () {
            $request = new DeleteComment('owner', 'repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::DELETE);
        });

        it('throws exception for comment ID less than 1', function () {
            new DeleteComment('owner', 'repo', 0);
        })->throws(InvalidArgumentException::class, 'Comment ID must be a positive integer');

        it('throws exception for negative comment ID', function () {
            new DeleteComment('owner', 'repo', -1);
        })->throws(InvalidArgumentException::class, 'Comment ID must be a positive integer');
    });
});
