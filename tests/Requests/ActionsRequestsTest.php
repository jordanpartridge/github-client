<?php

use JordanPartridge\GithubClient\Requests\Actions\GetWorkflowRuns;
use JordanPartridge\GithubClient\Requests\Actions\ListWorkflows;
use JordanPartridge\GithubClient\Requests\Actions\TriggerWorkflow;
use Saloon\Enums\Method;

describe('Actions Requests', function () {
    describe('ListWorkflows', function () {
        it('constructs with required parameters', function () {
            $request = new ListWorkflows('owner', 'repo');

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/actions/workflows');
        });

        it('uses GET method', function () {
            $request = new ListWorkflows('owner', 'repo');

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts pagination parameters', function () {
            $request = new ListWorkflows('owner', 'repo', per_page: 50, page: 2);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 50, 'page' => 2]);
        });

        it('filters null values from query parameters', function () {
            $request = new ListWorkflows('owner', 'repo', per_page: 30);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['per_page' => 30]);
            expect($query)->not->toHaveKey('page');
        });

        it('throws exception for per_page less than 1', function () {
            new ListWorkflows('owner', 'repo', per_page: 0);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('throws exception for per_page greater than 100', function () {
            new ListWorkflows('owner', 'repo', per_page: 101);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('accepts per_page at boundaries', function () {
            $request1 = new ListWorkflows('owner', 'repo', per_page: 1);
            $request100 = new ListWorkflows('owner', 'repo', per_page: 100);

            expect($request1)->toBeInstanceOf(ListWorkflows::class);
            expect($request100)->toBeInstanceOf(ListWorkflows::class);
        });
    });

    describe('GetWorkflowRuns', function () {
        it('constructs with required parameters', function () {
            $request = new GetWorkflowRuns('owner', 'repo', 12345);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/actions/workflows/12345/runs');
        });

        it('uses GET method', function () {
            $request = new GetWorkflowRuns('owner', 'repo', 12345);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::GET);
        });

        it('accepts all optional parameters', function () {
            $request = new GetWorkflowRuns(
                'owner',
                'repo',
                12345,
                per_page: 50,
                page: 2,
                status: 'completed',
                conclusion: 'success',
                branch: 'main',
            );

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe([
                'per_page' => 50,
                'page' => 2,
                'status' => 'completed',
                'conclusion' => 'success',
                'branch' => 'main',
            ]);
        });

        it('filters null values from query parameters', function () {
            $request = new GetWorkflowRuns('owner', 'repo', 12345, status: 'completed');

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultQuery');
            $method->setAccessible(true);
            $query = $method->invoke($request);

            expect($query)->toBe(['status' => 'completed']);
        });

        it('throws exception for per_page less than 1', function () {
            new GetWorkflowRuns('owner', 'repo', 12345, per_page: 0);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('throws exception for per_page greater than 100', function () {
            new GetWorkflowRuns('owner', 'repo', 12345, per_page: 101);
        })->throws(InvalidArgumentException::class, 'Per page must be between 1 and 100');

        it('validates status parameter', function () {
            new GetWorkflowRuns('owner', 'repo', 12345, status: 'invalid_status');
        })->throws(InvalidArgumentException::class, 'Invalid status provided');

        it('accepts all valid status values', function () {
            $validStatuses = [
                'completed', 'action_required', 'cancelled', 'failure', 'neutral',
                'skipped', 'stale', 'success', 'timed_out', 'in_progress', 'queued',
                'requested', 'waiting',
            ];

            foreach ($validStatuses as $status) {
                $request = new GetWorkflowRuns('owner', 'repo', 12345, status: $status);
                expect($request)->toBeInstanceOf(GetWorkflowRuns::class);
            }
        });

        it('validates conclusion parameter', function () {
            new GetWorkflowRuns('owner', 'repo', 12345, conclusion: 'invalid_conclusion');
        })->throws(InvalidArgumentException::class, 'Invalid conclusion provided');

        it('accepts all valid conclusion values', function () {
            $validConclusions = [
                'action_required', 'cancelled', 'failure', 'neutral', 'success',
                'skipped', 'stale', 'timed_out',
            ];

            foreach ($validConclusions as $conclusion) {
                $request = new GetWorkflowRuns('owner', 'repo', 12345, conclusion: $conclusion);
                expect($request)->toBeInstanceOf(GetWorkflowRuns::class);
            }
        });
    });

    describe('TriggerWorkflow', function () {
        it('constructs with required parameters', function () {
            $request = new TriggerWorkflow('owner', 'repo', 12345, ['ref' => 'main']);

            expect($request->resolveEndpoint())->toBe('/repos/owner/repo/actions/workflows/12345/dispatches');
        });

        it('uses POST method', function () {
            $request = new TriggerWorkflow('owner', 'repo', 12345, ['ref' => 'main']);

            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('method');
            $property->setAccessible(true);

            expect($property->getValue($request))->toBe(Method::POST);
        });

        it('returns body data from defaultBody', function () {
            $data = ['ref' => 'main', 'inputs' => ['env' => 'production']];
            $request = new TriggerWorkflow('owner', 'repo', 12345, $data);

            $reflection = new ReflectionClass($request);
            $method = $reflection->getMethod('defaultBody');
            $method->setAccessible(true);
            $body = $method->invoke($request);

            expect($body)->toBe($data);
        });

        it('throws exception when ref is missing', function () {
            new TriggerWorkflow('owner', 'repo', 12345, []);
        })->throws(InvalidArgumentException::class, 'The "ref" field is required for workflow dispatch');

        it('throws exception when ref is empty', function () {
            new TriggerWorkflow('owner', 'repo', 12345, ['ref' => '']);
        })->throws(InvalidArgumentException::class, 'The "ref" field is required for workflow dispatch');

        it('throws exception when inputs is not an array', function () {
            new TriggerWorkflow('owner', 'repo', 12345, ['ref' => 'main', 'inputs' => 'invalid']);
        })->throws(InvalidArgumentException::class, 'The "inputs" field must be an array');

        it('accepts workflow dispatch without inputs', function () {
            $request = new TriggerWorkflow('owner', 'repo', 12345, ['ref' => 'main']);
            expect($request)->toBeInstanceOf(TriggerWorkflow::class);
        });

        it('accepts workflow dispatch with empty inputs array', function () {
            $request = new TriggerWorkflow('owner', 'repo', 12345, ['ref' => 'main', 'inputs' => []]);
            expect($request)->toBeInstanceOf(TriggerWorkflow::class);
        });
    });
});
