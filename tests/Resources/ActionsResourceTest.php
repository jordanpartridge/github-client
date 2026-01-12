<?php

use JordanPartridge\GithubClient\Facades\Github;
use JordanPartridge\GithubClient\Resources\ActionsResource;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config(['github-client.token' => 'fake-token']);
});

describe('ActionsResource comprehensive tests', function () {
    it('can access actions resource through Github facade', function () {
        $resource = Github::actions();

        expect($resource)->toBeInstanceOf(ActionsResource::class);
    });

    describe('listWorkflows method', function () {
        it('can list workflows with all parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'total_count' => 2,
                    'workflows' => [
                        [
                            'id' => 161335,
                            'name' => 'CI',
                            'path' => '.github/workflows/ci.yml',
                            'state' => 'active',
                            'created_at' => '2024-01-01T00:00:00Z',
                            'updated_at' => '2024-01-01T00:00:00Z',
                        ],
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->listWorkflows(
                owner: 'owner',
                repo: 'repo',
                per_page: 50,
                page: 1,
            );

            expect($response->status())->toBe(200)
                ->and($response->json())->toHaveKey('workflows');
        });

        it('handles empty workflows list', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'total_count' => 0,
                    'workflows' => [],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->listWorkflows('owner', 'repo');

            expect($response->json()['total_count'])->toBe(0)
                ->and($response->json()['workflows'])->toBeEmpty();
        });

        it('handles null pagination parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['workflows' => []], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->listWorkflows(
                owner: 'owner',
                repo: 'repo',
                per_page: null,
                page: null,
            );

            expect($response->status())->toBe(200);
        });
    });

    describe('getWorkflowRuns method', function () {
        it('can get workflow runs with all parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'total_count' => 1,
                    'workflow_runs' => [
                        [
                            'id' => 30433642,
                            'name' => 'Build',
                            'head_branch' => 'main',
                            'head_sha' => 'abc123',
                            'status' => 'completed',
                            'conclusion' => 'success',
                            'workflow_id' => 161335,
                        ],
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->getWorkflowRuns(
                owner: 'owner',
                repo: 'repo',
                workflow_id: 161335,
                per_page: 20,
                page: 1,
                status: 'completed',
                conclusion: 'success',
                branch: 'main',
            );

            expect($response->status())->toBe(200)
                ->and($response->json())->toHaveKey('workflow_runs');
        });

        it('handles all valid status values', function () {
            $validStatuses = [
                'completed',
                'action_required',
                'cancelled',
                'failure',
                'neutral',
                'skipped',
                'stale',
                'success',
                'timed_out',
                'in_progress',
                'queued',
                'requested',
                'waiting',
            ];

            foreach ($validStatuses as $status) {
                $mockClient = new MockClient([
                    '*' => MockResponse::make(['workflow_runs' => []], 200),
                ]);

                Github::connector()->withMockClient($mockClient);

                $response = Github::actions()->getWorkflowRuns(
                    'owner',
                    'repo',
                    161335,
                    status: $status,
                );

                expect($response->status())->toBe(200);
            }
        });

        it('handles all valid conclusion values', function () {
            $validConclusions = [
                'action_required',
                'cancelled',
                'failure',
                'neutral',
                'success',
                'skipped',
                'stale',
                'timed_out',
            ];

            foreach ($validConclusions as $conclusion) {
                $mockClient = new MockClient([
                    '*' => MockResponse::make(['workflow_runs' => []], 200),
                ]);

                Github::connector()->withMockClient($mockClient);

                $response = Github::actions()->getWorkflowRuns(
                    'owner',
                    'repo',
                    161335,
                    conclusion: $conclusion,
                );

                expect($response->status())->toBe(200);
            }
        });

        it('handles null optional parameters', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make(['workflow_runs' => []], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->getWorkflowRuns(
                'owner',
                'repo',
                161335,
                per_page: null,
                page: null,
                status: null,
                conclusion: null,
                branch: null,
            );

            expect($response->status())->toBe(200);
        });
    });

    describe('triggerWorkflow method', function () {
        it('can trigger workflow with ref only', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 204),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->triggerWorkflow(
                'owner',
                'repo',
                161335,
                ['ref' => 'main'],
            );

            expect($response->status())->toBe(204);
        });

        it('can trigger workflow with inputs', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([], 204),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->triggerWorkflow(
                'owner',
                'repo',
                161335,
                [
                    'ref' => 'main',
                    'inputs' => [
                        'environment' => 'production',
                        'debug' => 'false',
                        'version' => '1.0.0',
                    ],
                ],
            );

            expect($response->status())->toBe(204);
        });

        it('can trigger workflow with different refs', function () {
            $refs = ['main', 'develop', 'feature/test', 'v1.0.0', 'refs/heads/main'];

            foreach ($refs as $ref) {
                $mockClient = new MockClient([
                    '*' => MockResponse::make([], 204),
                ]);

                Github::connector()->withMockClient($mockClient);

                $response = Github::actions()->triggerWorkflow(
                    'owner',
                    'repo',
                    161335,
                    ['ref' => $ref],
                );

                expect($response->status())->toBe(204);
            }
        });
    });

    describe('workflow states', function () {
        it('can list active workflows', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'workflows' => [
                        ['id' => 1, 'state' => 'active'],
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->listWorkflows('owner', 'repo');
            $workflows = $response->json()['workflows'];

            expect($workflows[0]['state'])->toBe('active');
        });

        it('can list disabled workflows', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'workflows' => [
                        ['id' => 1, 'state' => 'disabled_manually'],
                    ],
                ], 200),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->listWorkflows('owner', 'repo');
            $workflows = $response->json()['workflows'];

            expect($workflows[0]['state'])->toBe('disabled_manually');
        });
    });

    describe('error handling', function () {
        it('handles repository not found', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'message' => 'Not Found',
                    'documentation_url' => 'https://docs.github.com/rest',
                ], 404),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->listWorkflows('nonexistent', 'repo');

            expect($response->status())->toBe(404);
        });

        it('handles workflow not found', function () {
            $mockClient = new MockClient([
                '*' => MockResponse::make([
                    'message' => 'Not Found',
                ], 404),
            ]);

            Github::connector()->withMockClient($mockClient);

            $response = Github::actions()->getWorkflowRuns('owner', 'repo', 999999);

            expect($response->status())->toBe(404);
        });
    });
});
