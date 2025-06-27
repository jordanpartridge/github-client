<?php

namespace JordanPartridge\GithubClient\Data;

class VerificationData
{
    public function __construct(
        public bool $verified,
        public string $reason,
        public ?string $signature,
        public ?string $payload,
        public ?string $verified_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            verified: $data['verified'],
            reason: $data['reason'],
            signature: $data['signature'] ?? null,
            payload: $data['payload'] ?? null,
            verified_at: $data['verified_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'verified' => $this->verified,
            'reason' => $this->reason,
            'signature' => $this->signature,
            'payload' => $this->payload,
            'verified_at' => $this->verified_at,
        ];
    }
}
