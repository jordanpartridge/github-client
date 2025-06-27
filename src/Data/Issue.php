<?php

namespace JordanPartridge\GithubClient\Data;

class Issue
{
    public function __construct(
        public readonly int $id,
        public readonly string $node_id,
        public readonly string $url,
        public readonly string $repository_url,
        public readonly string $labels_url,
        public readonly string $comments_url,
        public readonly string $events_url,
        public readonly string $html_url,
        public readonly int $number,
        public readonly string $state,
        public readonly string $title,
        public readonly ?string $body,
        public readonly GitUserData $user,
        public readonly array $labels,
        public readonly ?GitUserData $assignee,
        public readonly array $assignees,
        public readonly mixed $milestone,
        public readonly int $comments,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly ?string $closed_at,
        public readonly ?GitUserData $closed_by,
        public readonly ?string $author_association,
        public readonly ?string $active_lock_reason,
        public readonly bool $locked,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'],
            node_id: $data['node_id'],
            url: $data['url'],
            repository_url: $data['repository_url'],
            labels_url: $data['labels_url'],
            comments_url: $data['comments_url'],
            events_url: $data['events_url'],
            html_url: $data['html_url'],
            number: $data['number'],
            state: $data['state'],
            title: $data['title'],
            body: $data['body'] ?? null,
            user: GitUserData::fromArray($data['user']),
            labels: $data['labels'] ?? [],
            assignee: isset($data['assignee']) ? GitUserData::fromArray($data['assignee']) : null,
            assignees: array_map(
                fn (array $assignee) => GitUserData::fromArray($assignee),
                $data['assignees'] ?? []
            ),
            milestone: $data['milestone'] ?? null,
            comments: $data['comments'],
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
            closed_at: $data['closed_at'] ?? null,
            closed_by: isset($data['closed_by']) ? GitUserData::fromArray($data['closed_by']) : null,
            author_association: $data['author_association'] ?? null,
            active_lock_reason: $data['active_lock_reason'] ?? null,
            locked: $data['locked'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'node_id' => $this->node_id,
            'url' => $this->url,
            'repository_url' => $this->repository_url,
            'labels_url' => $this->labels_url,
            'comments_url' => $this->comments_url,
            'events_url' => $this->events_url,
            'html_url' => $this->html_url,
            'number' => $this->number,
            'state' => $this->state,
            'title' => $this->title,
            'body' => $this->body,
            'user' => $this->user->toArray(),
            'labels' => $this->labels,
            'assignee' => $this->assignee?->toArray(),
            'assignees' => array_map(fn (GitUserData $assignee) => $assignee->toArray(), $this->assignees),
            'milestone' => $this->milestone,
            'comments' => $this->comments,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->closed_at,
            'closed_by' => $this->closed_by?->toArray(),
            'author_association' => $this->author_association,
            'active_lock_reason' => $this->active_lock_reason,
            'locked' => $this->locked,
        ];
    }
}
