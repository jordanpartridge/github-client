<?php

namespace JordanPartridge\GithubClient\DataTransferObjects;

use Saloon\Contracts\Response;

abstract class AbstractDTO
{
    abstract public static function fromArray(array $data): self;

    abstract public static function fromResponse(Response $response): self;

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
