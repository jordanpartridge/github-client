<?php

namespace JordanPartridge\GithubClient\Requests\Pulls;

use JordanPartridge\GithubClient\Data\Pulls\PullRequestCommentDTO;
use JordanPartridge\GithubClient\ValueObjects\Repo;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Enhanced PR comment fetching with filtering capabilities.
 */
class CommentsWithFilters extends Request
{
    protected Method $method = Method::GET;

    private string $repo;
    private string $owner;
    private int $number;
    private array $filters;

    public function __construct(string $owner_repo, int $number, array $filters = [])
    {
        $validated = Repo::fromFullName($owner_repo);
        $this->owner = $validated->owner();
        $this->repo = $validated->name();
        $this->number = $number;
        $this->filters = $filters;
    }

    public function createDtoFromResponse(Response $response): array
    {
        $comments = array_map(
            fn (array $comment) => PullRequestCommentDTO::fromApiResponse($comment),
            $response->json()
        );

        return $this->applyFilters($comments);
    }

    public function resolveEndpoint(): string
    {
        return sprintf('repos/%s/%s/pulls/%d/comments', $this->owner, $this->repo, $this->number);
    }

    protected function defaultQuery(): array
    {
        $query = [];

        // Apply pagination settings
        if (isset($this->filters['per_page'])) {
            $query['per_page'] = min((int) $this->filters['per_page'], 100);
        }

        if (isset($this->filters['page'])) {
            $query['page'] = (int) $this->filters['page'];
        }

        return $query;
    }

    private function applyFilters(array $comments): array
    {
        if (empty($this->filters)) {
            return $comments;
        }

        return array_values(array_filter($comments, function (PullRequestCommentDTO $comment) {
            return $this->matchesFilters($comment);
        }));
    }

    private function matchesFilters(PullRequestCommentDTO $comment): bool
    {
        // Author filtering
        if (isset($this->filters['author'])) {
            $targetAuthor = strtolower($this->filters['author']);
            $commentAuthor = strtolower($comment->user->login);
            
            if ($commentAuthor !== $targetAuthor) {
                return false;
            }
        }

        // Author type filtering (bot vs human)
        if (isset($this->filters['author_type'])) {
            $authorType = $this->filters['author_type'];
            $isBot = $this->isBot($comment->user->login);
            
            if ($authorType === 'bot' && !$isBot) {
                return false;
            }
            
            if ($authorType === 'human' && $isBot) {
                return false;
            }
        }

        // Severity filtering
        if (isset($this->filters['severity'])) {
            $targetSeverity = strtolower($this->filters['severity']);
            $commentSeverity = $comment->metadata?->severity;
            
            if ($commentSeverity !== $targetSeverity) {
                return false;
            }
        }

        // File path filtering
        if (isset($this->filters['file_path'])) {
            $targetPath = $this->filters['file_path'];
            
            if ($comment->path !== $targetPath) {
                return false;
            }
        }

        // Date range filtering
        if (isset($this->filters['since'])) {
            $since = new \DateTimeImmutable($this->filters['since']);
            $commentDate = new \DateTimeImmutable($comment->created_at);
            
            if ($commentDate < $since) {
                return false;
            }
        }

        if (isset($this->filters['until'])) {
            $until = new \DateTimeImmutable($this->filters['until']);
            $commentDate = new \DateTimeImmutable($comment->created_at);
            
            if ($commentDate > $until) {
                return false;
            }
        }

        // Claim type filtering
        if (isset($this->filters['claim_type'])) {
            $targetClaimType = $this->filters['claim_type'];
            $commentClaimType = $comment->metadata?->claim_type;
            
            if ($commentClaimType !== $targetClaimType) {
                return false;
            }
        }

        // Content filtering
        if (isset($this->filters['contains'])) {
            $needle = strtolower($this->filters['contains']);
            $haystack = strtolower($comment->body);
            
            if (strpos($haystack, $needle) === false) {
                return false;
            }
        }

        return true;
    }

    private function isBot(string $username): bool
    {
        $botPatterns = [
            '/\[bot\]$/',
            '/bot$/i',
            '/github-actions/',
            '/dependabot/',
            '/codecov/',
            '/sonarqubecloud/',
            '/coderabbitai/',
        ];

        foreach ($botPatterns as $pattern) {
            if (preg_match($pattern, $username)) {
                return true;
            }
        }

        return false;
    }
}