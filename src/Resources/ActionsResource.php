<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Requests\Actions\GetWorkflowRuns;
use JordanPartridge\GithubClient\Requests\Actions\ListWorkflows;
use JordanPartridge\GithubClient\Requests\Actions\TriggerWorkflow;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Http\Response;

/**
 * GitHub Actions Resource Handler
 *
 * This class provides methods to interact with GitHub's Actions endpoints.
 * It handles operations such as listing workflows, getting workflow runs,
 * and triggering workflows.
 *
 * @link https://docs.github.com/en/rest/actions/workflows Documentation for GitHub Actions API
 *
 * Usage example:
 * ```php
 * $actions = new ActionsResource($connector);
 *
 * // List workflows for a repository
 * $workflows = $actions->listWorkflows('owner/repo');
 *
 * // Get workflow runs
 * $runs = $actions->getWorkflowRuns('owner/repo', $workflow_id);
 *
 * // Trigger a workflow
 * $result = $actions->triggerWorkflow('owner/repo', $workflow_id, [
 *     'ref' => 'main',
 *     'inputs' => ['test' => 'value']
 * ]);
 * ```
 */
readonly class ActionsResource extends BaseResource
{
    /**
     * List workflows for a repository
     *
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int|null  $per_page  Number of results per page (max 100)
     * @param  int|null  $page  Page number of the results to fetch
     * @return Response Returns a Saloon response containing workflow data
     *
     * @link https://docs.github.com/en/rest/actions/workflows#list-repository-workflows
     */
    public function listWorkflows(
        string $owner,
        string $repo,
        ?int $per_page = null,
        ?int $page = null
    ): Response {
        return $this->github()->connector()->send(new ListWorkflows(
            owner: $owner,
            repo: $repo,
            per_page: $per_page,
            page: $page
        ));
    }

    /**
     * Get workflow runs for a workflow
     *
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int  $workflow_id  The ID of the workflow
     * @param  int|null  $per_page  Number of results per page (max 100)
     * @param  int|null  $page  Page number of the results to fetch
     * @param  string|null  $status  Filter by status (completed, action_required, cancelled, failure, neutral, skipped, stale, success, timed_out, in_progress, queued, requested, waiting)
     * @param  string|null  $conclusion  Filter by conclusion (action_required, cancelled, failure, neutral, success, skipped, stale, timed_out)
     * @param  string|null  $branch  Filter by branch name
     * @return Response Returns a Saloon response containing workflow run data
     *
     * @link https://docs.github.com/en/rest/actions/workflow-runs#list-workflow-runs-for-a-workflow
     */
    public function getWorkflowRuns(
        string $owner,
        string $repo,
        int $workflow_id,
        ?int $per_page = null,
        ?int $page = null,
        ?string $status = null,
        ?string $conclusion = null,
        ?string $branch = null
    ): Response {
        return $this->github()->connector()->send(new GetWorkflowRuns(
            owner: $owner,
            repo: $repo,
            workflow_id: $workflow_id,
            per_page: $per_page,
            page: $page,
            status: $status,
            conclusion: $conclusion,
            branch: $branch
        ));
    }

    /**
     * Trigger a workflow dispatch event
     *
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int  $workflow_id  The ID of the workflow
     * @param  array  $data  The workflow dispatch data including 'ref' and optional 'inputs'
     * @return Response Returns a Saloon response
     *
     * @link https://docs.github.com/en/rest/actions/workflows#create-a-workflow-dispatch-event
     */
    public function triggerWorkflow(
        string $owner,
        string $repo,
        int $workflow_id,
        array $data = []
    ): Response {
        return $this->github()->connector()->send(new TriggerWorkflow(
            owner: $owner,
            repo: $repo,
            workflow_id: $workflow_id,
            data: $data
        ));
    }
}
