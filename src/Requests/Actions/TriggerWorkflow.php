<?php

namespace JordanPartridge\GithubClient\Requests\Actions;

use InvalidArgumentException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class TriggerWorkflow extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  string  $owner  The account owner of the repository
     * @param  string  $repo  The name of the repository
     * @param  int  $workflow_id  The ID of the workflow
     * @param  array  $data  The workflow dispatch data including 'ref' and optional 'inputs'
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $workflow_id,
        protected array $data = [],
    ) {
        // Validate that 'ref' is provided in data
        if (! isset($this->data['ref']) || empty($this->data['ref'])) {
            throw new InvalidArgumentException('The "ref" field is required for workflow dispatch');
        }

        // Ensure inputs is an array if provided
        if (isset($this->data['inputs']) && ! is_array($this->data['inputs'])) {
            throw new InvalidArgumentException('The "inputs" field must be an array');
        }
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/actions/workflows/{$this->workflow_id}/dispatches";
    }
}
