<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Requests\Checks\GetCheckRunsForRef;
use JordanPartridge\GithubClient\Requests\Checks\GetCombinedStatus;

readonly class ChecksResource extends BaseResource
{
    /**
     * Get check runs for a commit reference (SHA, branch, or tag).
     *
     * @return array{total_count: int, check_runs: array<int, array<string, mixed>>}
     */
    public function forRef(string $owner, string $repo, string $ref, ?int $perPage = null, ?int $page = null): array
    {
        $response = $this->github()->connector()->send(
            new GetCheckRunsForRef($owner, $repo, $ref, $perPage, $page),
        );

        return $response->json();
    }

    /**
     * Get the combined commit status for a reference.
     *
     * Returns the overall state (success, failure, pending) and individual statuses.
     *
     * @return array{state: string, statuses: array<int, array<string, mixed>>, total_count: int}
     */
    public function combinedStatus(string $owner, string $repo, string $ref): array
    {
        $response = $this->github()->connector()->send(
            new GetCombinedStatus($owner, $repo, $ref),
        );

        return $response->json();
    }

    /**
     * Check if all checks pass for a given ref.
     */
    public function allPassing(string $owner, string $repo, string $ref): bool
    {
        $checkRuns = $this->forRef($owner, $repo, $ref);

        if (empty($checkRuns['check_runs'])) {
            return true;
        }

        foreach ($checkRuns['check_runs'] as $run) {
            if ($run['status'] !== 'completed') {
                return false;
            }

            if ($run['conclusion'] !== 'success' && $run['conclusion'] !== 'skipped') {
                return false;
            }
        }

        return true;
    }
}
