<?php

use JordanPartridge\GithubClient\Enums\MergeMethod;
use JordanPartridge\GithubClient\Requests\Pulls\Comments;
use JordanPartridge\GithubClient\Requests\Pulls\CommentsWithFilters;
use JordanPartridge\GithubClient\Requests\Pulls\Create;
use JordanPartridge\GithubClient\Requests\Pulls\CreateComment;
use JordanPartridge\GithubClient\Requests\Pulls\CreateReview;
use JordanPartridge\GithubClient\Requests\Pulls\DeleteComment;
use JordanPartridge\GithubClient\Requests\Pulls\Files;
use JordanPartridge\GithubClient\Requests\Pulls\Get;
use JordanPartridge\GithubClient\Requests\Pulls\GetComment;
use JordanPartridge\GithubClient\Requests\Pulls\GetWithDetailDTO;
use JordanPartridge\GithubClient\Requests\Pulls\Index;
use JordanPartridge\GithubClient\Requests\Pulls\IndexWithSummaryDTO;
use JordanPartridge\GithubClient\Requests\Pulls\Merge;
use JordanPartridge\GithubClient\Requests\Pulls\Reviews;
use JordanPartridge\GithubClient\Requests\Pulls\Update;
use JordanPartridge\GithubClient\Requests\Pulls\UpdateComment;
use Saloon\Enums\Method;

describe('Pulls Requests', function () {
    describe('Pulls\Index', function () {
        it('constructs with required parameters', function () {
            $request = new Index('owner/repo');

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls');
        });

        it('uses GET method', function () {
            $request = new Index('owner/repo');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('validates repo name format', function () {
            new Index('invalid-repo');
        })->throws(InvalidArgumentException::class);

        it('accepts parameters array', function () {
            $request = new Index('owner/repo', ['state' => 'open', 'per_page' => 50]);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toHaveKey('state');
            expect($query)->toHaveKey('per_page');
        });

        it('has createDtoFromResponse method', function () {
            $request = new Index('owner/repo');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\Get', function () {
        it('constructs with required parameters', function () {
            $request = new Get('owner/repo', 42);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42');
        });

        it('uses GET method', function () {
            $request = new Get('owner/repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('validates repo name format', function () {
            new Get('invalid-repo', 1);
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new Get('owner/repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\Create', function () {
        it('constructs with required parameters', function () {
            $request = new Create('owner/repo', 'Title', 'feature-branch', 'main');

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls');
        });

        it('uses POST method', function () {
            $request = new Create('owner/repo', 'Title', 'head', 'base');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::POST);
        });

        it('includes all body parameters', function () {
            $request = new Create(
                'owner/repo',
                'PR Title',
                'feature-branch',
                'main',
                bodyText: 'PR description',
                draft: true,
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe([
                'title' => 'PR Title',
                'head' => 'feature-branch',
                'base' => 'main',
                'body' => 'PR description',
                'draft' => true,
            ]);
        });

        it('validates repo name format', function () {
            new Create('invalid', 'Title', 'head', 'base');
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new Create('owner/repo', 'Title', 'head', 'base');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\Update', function () {
        it('constructs with required parameters', function () {
            $request = new Update('owner/repo', 42);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42');
        });

        it('uses PATCH method', function () {
            $request = new Update('owner/repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::PATCH);
        });

        it('includes parameters in body', function () {
            $request = new Update('owner/repo', 42, ['title' => 'New Title', 'state' => 'closed']);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe(['title' => 'New Title', 'state' => 'closed']);
        });

        it('validates repo name format', function () {
            new Update('invalid', 1);
        })->throws(InvalidArgumentException::class);
    });

    describe('Pulls\Merge', function () {
        it('constructs with required parameters', function () {
            $request = new Merge('owner/repo', 42);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42/merge');
        });

        it('uses PUT method', function () {
            $request = new Merge('owner/repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::PUT);
        });

        it('includes all body parameters', function () {
            $request = new Merge(
                'owner/repo',
                42,
                commitMessage: 'Merge commit message',
                sha: 'abc123',
                mergeMethod: MergeMethod::Squash,
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe([
                'commit_message' => 'Merge commit message',
                'sha' => 'abc123',
                'merge_method' => 'squash',
            ]);
        });

        it('uses merge method as default', function () {
            $request = new Merge('owner/repo', 42);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toHaveKey('merge_method', 'merge');
        });

        it('supports all merge methods', function () {
            $mergeMethods = [MergeMethod::Merge, MergeMethod::Squash, MergeMethod::Rebase];

            foreach ($mergeMethods as $mergeMethod) {
                $request = new Merge('owner/repo', 42, mergeMethod: $mergeMethod);
                expect($request)->toBeInstanceOf(Merge::class);
            }
        });

        it('validates repo name format', function () {
            new Merge('invalid', 1);
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new Merge('owner/repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\Comments', function () {
        it('constructs with required parameters', function () {
            $request = new Comments('owner/repo', 42);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42/comments');
        });

        it('uses GET method', function () {
            $request = new Comments('owner/repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('validates repo name format', function () {
            new Comments('invalid', 1);
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new Comments('owner/repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\GetComment', function () {
        it('constructs with required parameters', function () {
            $request = new GetComment('owner', 'repo', 123);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/comments/123');
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

    describe('Pulls\CreateComment', function () {
        it('constructs with required parameters', function () {
            $request = new CreateComment('owner/repo', 42, 'Comment body', 'abc123', 'file.php', 10);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42/comments');
        });

        it('uses POST method', function () {
            $request = new CreateComment('owner/repo', 1, 'Body', 'sha', 'file', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::POST);
        });

        it('includes all body parameters', function () {
            $request = new CreateComment('owner/repo', 42, 'Comment body', 'abc123', 'file.php', 10);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe([
                'body' => 'Comment body',
                'commit_id' => 'abc123',
                'path' => 'file.php',
                'position' => 10,
            ]);
        });

        it('validates repo name format', function () {
            new CreateComment('invalid', 1, 'Body', 'sha', 'file', 1);
        })->throws(InvalidArgumentException::class);
    });

    describe('Pulls\UpdateComment', function () {
        it('constructs with required parameters', function () {
            $request = new UpdateComment('owner', 'repo', 123, 'Updated body');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/comments/123');
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

    describe('Pulls\DeleteComment', function () {
        it('constructs with required parameters', function () {
            $request = new DeleteComment('owner', 'repo', 123);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/comments/123');
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
    });

    describe('Pulls\Files', function () {
        it('constructs with required parameters', function () {
            $request = new Files('owner/repo', 42);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42/files');
        });

        it('uses GET method', function () {
            $request = new Files('owner/repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('validates repo name format', function () {
            new Files('invalid', 1);
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new Files('owner/repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\Reviews', function () {
        it('constructs with required parameters', function () {
            $request = new Reviews('owner/repo', 42);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42/reviews');
        });

        it('uses GET method', function () {
            $request = new Reviews('owner/repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('validates repo name format', function () {
            new Reviews('invalid', 1);
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new Reviews('owner/repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\CreateReview', function () {
        it('constructs with required parameters', function () {
            $request = new CreateReview('owner/repo', 42, 'Review body');

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42/reviews');
        });

        it('uses POST method', function () {
            $request = new CreateReview('owner/repo', 1, 'Body');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::POST);
        });

        it('includes all body parameters', function () {
            $comments = [['path' => 'file.php', 'position' => 5, 'body' => 'Comment']];
            $request = new CreateReview(
                'owner/repo',
                42,
                'Review body',
                event: 'APPROVE',
                comments: $comments,
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe([
                'body' => 'Review body',
                'event' => 'APPROVE',
                'comments' => $comments,
            ]);
        });

        it('uses COMMENT as default event', function () {
            $request = new CreateReview('owner/repo', 42, 'Body');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body['event'])->toBe('COMMENT');
        });

        it('validates repo name format', function () {
            new CreateReview('invalid', 1, 'Body');
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new CreateReview('owner/repo', 1, 'Body');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\CommentsWithFilters', function () {
        it('constructs with required parameters', function () {
            $request = new CommentsWithFilters('owner/repo', 42);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42/comments');
        });

        it('uses GET method', function () {
            $request = new CommentsWithFilters('owner/repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts filter parameters', function () {
            $request = new CommentsWithFilters('owner/repo', 42, ['per_page' => 50, 'page' => 2]);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 50, 'page' => 2]);
        });

        it('limits per_page to 100', function () {
            $request = new CommentsWithFilters('owner/repo', 42, ['per_page' => 150]);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query['per_page'])->toBe(100);
        });

        it('validates repo name format', function () {
            new CommentsWithFilters('invalid', 1);
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new CommentsWithFilters('owner/repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\GetWithDetailDTO', function () {
        it('constructs with required parameters', function () {
            $request = new GetWithDetailDTO('owner/repo', 42);

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls/42');
        });

        it('uses GET method', function () {
            $request = new GetWithDetailDTO('owner/repo', 1);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('validates repo name format', function () {
            new GetWithDetailDTO('invalid', 1);
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new GetWithDetailDTO('owner/repo', 1);

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });

    describe('Pulls\IndexWithSummaryDTO', function () {
        it('constructs with required parameters', function () {
            $request = new IndexWithSummaryDTO('owner/repo');

            expect($request->resolveEndpoint())->toBe('repos/owner/repo/pulls');
        });

        it('uses GET method', function () {
            $request = new IndexWithSummaryDTO('owner/repo');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts parameters array', function () {
            $request = new IndexWithSummaryDTO('owner/repo', ['state' => 'open', 'per_page' => 50]);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toHaveKey('state');
            expect($query)->toHaveKey('per_page');
        });

        it('validates repo name format', function () {
            new IndexWithSummaryDTO('invalid');
        })->throws(InvalidArgumentException::class);

        it('has createDtoFromResponse method', function () {
            $request = new IndexWithSummaryDTO('owner/repo');

            expect(method_exists($request, 'createDtoFromResponse'))->toBeTrue();
        });
    });
});
