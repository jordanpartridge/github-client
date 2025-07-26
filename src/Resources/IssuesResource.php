<?php

namespace JordanPartridge\GithubClient\Resources;

use JordanPartridge\GithubClient\Data\Issues\IssueCommentDTO;
use JordanPartridge\GithubClient\Data\Issues\IssueDTO;
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
use Saloon\Http\Response;

readonly class IssuesResource extends BaseResource
{
    /**
     * List issues assigned to the authenticated user across all owned and member repositories
     */
    public function all(
        ?int $per_page = 100,
        ?int $page = null,
        ?State $state = null,
        ?string $labels = null,
        ?Sort $sort = null,
        ?Direction $direction = null,
        ?string $assignee = null,
        ?string $creator = null,
        ?string $mentioned = null,
        ?string $since = null,
    ): Response {
        return $this->connector()->send(new Index(
            per_page: $per_page,
            page: $page,
            state: $state,
            labels: $labels,
            sort: $sort,
            direction: $direction,
            assignee: $assignee,
            creator: $creator,
            mentioned: $mentioned,
            since: $since,
        ));
    }

    /**
     * List issues for a specific repository
     */
    public function forRepo(
        string $owner,
        string $repo,
        ?int $per_page = 100,
        ?int $page = null,
        ?State $state = null,
        ?string $labels = null,
        ?Sort $sort = null,
        ?Direction $direction = null,
        ?string $assignee = null,
        ?string $creator = null,
        ?string $mentioned = null,
        ?string $since = null,
    ): Response {
        return $this->connector()->send(new RepoIndex(
            owner: $owner,
            repo: $repo,
            per_page: $per_page,
            page: $page,
            state: $state,
            labels: $labels,
            sort: $sort,
            direction: $direction,
            assignee: $assignee,
            creator: $creator,
            mentioned: $mentioned,
            since: $since,
        ));
    }

    /**
     * Get auto-paginated issues for a specific repository
     */
    public function allForRepo(
        string $owner,
        string $repo,
        ?int $per_page = 100,
        ?State $state = null,
        ?string $labels = null,
        ?Sort $sort = null,
        ?Direction $direction = null,
        ?string $assignee = null,
        ?string $creator = null,
        ?string $mentioned = null,
        ?string $since = null,
    ): array {
        $page = 1;
        $allIssues = [];
        $maxPages = 1000; // Prevent infinite loops

        do {
            if ($page > $maxPages) {
                throw new \RuntimeException("Maximum page limit ($maxPages) exceeded during pagination");
            }

            $response = $this->forRepo(
                owner: $owner,
                repo: $repo,
                per_page: $per_page,
                page: $page,
                state: $state,
                labels: $labels,
                sort: $sort,
                direction: $direction,
                assignee: $assignee,
                creator: $creator,
                mentioned: $mentioned,
                since: $since,
            );

            $issues = $response->dto();

            if (! empty($issues)) {
                $allIssues = array_merge($allIssues, $issues);
            }

            // Check if there are more pages by examining the Link header
            $linkHeader = $response->header('Link');
            $hasNextPage = $linkHeader && str_contains($linkHeader, 'rel="next"');

            $page++;
        } while ($hasNextPage && ! empty($issues));

        return $allIssues;
    }

    /**
     * Get a specific issue
     */
    public function get(string $owner, string $repo, int $issue_number): IssueDTO
    {
        $response = $this->connector()->send(new Get(
            owner: $owner,
            repo: $repo,
            issue_number: $issue_number,
        ));

        return $response->dto();
    }

    /**
     * Create a new issue
     */
    public function create(
        string $owner,
        string $repo,
        string $title,
        ?string $body = null,
        ?array $assignees = null,
        ?int $milestone = null,
        ?array $labels = null,
    ): IssueDTO {
        $response = $this->connector()->send(new Create(
            owner: $owner,
            repo: $repo,
            title: $title,
            bodyText: $body,
            assignees: $assignees,
            milestone: $milestone,
            labels: $labels,
        ));

        return $response->dto();
    }

    /**
     * Update an existing issue
     */
    public function update(
        string $owner,
        string $repo,
        int $issue_number,
        ?string $title = null,
        ?string $body = null,
        ?State $state = null,
        ?array $assignees = null,
        ?int $milestone = null,
        ?array $labels = null,
    ): IssueDTO {
        $response = $this->connector()->send(new Update(
            owner: $owner,
            repo: $repo,
            issue_number: $issue_number,
            title: $title,
            bodyText: $body,
            state: $state,
            assignees: $assignees,
            milestone: $milestone,
            labels: $labels,
        ));

        return $response->dto();
    }

    /**
     * Close an issue
     */
    public function close(string $owner, string $repo, int $issue_number): IssueDTO
    {
        return $this->update(
            owner: $owner,
            repo: $repo,
            issue_number: $issue_number,
            state: State::CLOSED
        );
    }

    /**
     * Reopen an issue
     */
    public function reopen(string $owner, string $repo, int $issue_number): IssueDTO
    {
        return $this->update(
            owner: $owner,
            repo: $repo,
            issue_number: $issue_number,
            state: State::OPEN
        );
    }

    /**
     * List comments for an issue
     */
    public function comments(
        string $owner,
        string $repo,
        int $issue_number,
        ?int $per_page = 100,
        ?int $page = null,
        ?string $since = null,
    ): array {
        $response = $this->connector()->send(new Comments(
            owner: $owner,
            repo: $repo,
            issue_number: $issue_number,
            per_page: $per_page,
            page: $page,
            since: $since,
        ));

        return $response->dto();
    }

    /**
     * Add a comment to an issue
     */
    public function addComment(
        string $owner,
        string $repo,
        int $issue_number,
        string $body,
    ): IssueCommentDTO {
        $response = $this->connector()->send(new CreateComment(
            owner: $owner,
            repo: $repo,
            issue_number: $issue_number,
            bodyText: $body,
        ));

        return $response->dto();
    }

    /**
     * Get a single issue comment by ID
     */
    public function getComment(
        string $owner,
        string $repo,
        int $commentId,
    ): IssueCommentDTO {
        $response = $this->connector()->send(new GetComment(
            owner: $owner,
            repo: $repo,
            commentId: $commentId,
        ));

        return $response->dto();
    }

    /**
     * Update an issue comment
     */
    public function updateComment(
        string $owner,
        string $repo,
        int $commentId,
        string $body,
    ): IssueCommentDTO {
        $response = $this->connector()->send(new UpdateComment(
            owner: $owner,
            repo: $repo,
            commentId: $commentId,
            bodyText: $body,
        ));

        return $response->dto();
    }

    /**
     * Delete an issue comment
     */
    public function deleteComment(
        string $owner,
        string $repo,
        int $commentId,
    ): bool {
        $response = $this->connector()->send(new DeleteComment(
            owner: $owner,
            repo: $repo,
            commentId: $commentId,
        ));

        return $response->successful();
    }
}
