<?php

namespace JordanPartridge\GithubClient\Contracts;

interface ResourceInterface
{
   public function __construct(GithubConnectorInterface $connector);
}
