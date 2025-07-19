<?php

namespace JordanPartridge\GithubClient\Data\Issues;

use JordanPartridge\GithubClient\Data\GitUserData;

class IssueDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $number,
        public readonly string $title,
        public readonly string $body,
        public readonly string $state,
        public readonly ?GitUserData $assignee,
        public readonly array $assignees,
        public readonly array $labels,
        public readonly int $comments,
        public readonly string $html_url,
        public readonly GitUserData $user,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly ?string $closed_at,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        // Filter out pull requests - GitHub's Issues API returns both issues and PRs
        if (isset($data['pull_request'])) {
            throw new \InvalidArgumentException('This is a pull request, not an issue');
        }

        return new self(
            id: $data['id'],
            number: $data['number'],
            title: $data['title'],
            body: $data['body'] ?? '',
            state: $data['state'],
            assignee: isset($data['assignee']) ? GitUserData::fromArray($data['assignee']) : null,
            assignees: array_map(
                fn (array $assignee) => GitUserData::fromArray($assignee),
                $data['assignees'] ?? []
            ),
            labels: array_map(
                fn (array $label) => LabelDTO::fromApiResponse($label),
                $data['labels'] ?? []
            ),
            comments: $data['comments'],
            html_url: $data['html_url'],
            user: GitUserData::fromArray($data['user']),
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
            closed_at: $data['closed_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'title' => $this->title,
            'body' => $this->body,
            'state' => $this->state,
            'assignee' => $this->assignee?->toArray(),
            'assignees' => array_map(fn (GitUserData $assignee) => $assignee->toArray(), $this->assignees),
            'labels' => array_map(fn (LabelDTO $label) => $label->toArray(), $this->labels),
            'comments' => $this->comments,
            'html_url' => $this->html_url,
            'user' => $this->user->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->closed_at,
        ];
    }
}
