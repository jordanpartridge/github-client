<?php

namespace JordanPartridge\GithubClient\Requests\Actions;

use InvalidArgumentException;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkflowRuns extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int  $workflow_id  The ID of the workflow
     * @param  int|null  $per_page  Items per page (max 100)
     * @param  int|null  $page  Page number
     * @param  string|null  $status  Filter by status
     * @param  string|null  $conclusion  Filter by conclusion
     * @param  string|null  $branch  Filter by branch name
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $workflow_id,
        protected ?int $per_page = null,
        protected ?int $page = null,
        protected ?string $status = null,
        protected ?string $conclusion = null,
        protected ?string $branch = null,
    ) {
        if ($this->per_page !== null && ($this->per_page < 1 || $this->per_page > 100)) {
            throw new InvalidArgumentException('Per page must be between 1 and 100');
        }

        // Validate status if provided
        $validStatuses = [
            'completed', 'action_required', 'cancelled', 'failure', 'neutral',
            'skipped', 'stale', 'success', 'timed_out', 'in_progress', 'queued',
            'requested', 'waiting',
        ];
        if ($this->status !== null && ! in_array($this->status, $validStatuses)) {
            throw new InvalidArgumentException('Invalid status provided');
        }

        // Validate conclusion if provided
        $validConclusions = [
            'action_required', 'cancelled', 'failure', 'neutral', 'success',
            'skipped', 'stale', 'timed_out',
        ];
        if ($this->conclusion !== null && ! in_array($this->conclusion, $validConclusions)) {
            throw new InvalidArgumentException('Invalid conclusion provided');
        }
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'per_page' => $this->per_page,
            'page' => $this->page,
            'status' => $this->status,
            'conclusion' => $this->conclusion,
            'branch' => $this->branch,
        ], fn ($value) => $value !== null);
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/actions/workflows/{$this->workflow_id}/runs";
    }
}
