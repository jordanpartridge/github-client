<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use JordanPartridge\GithubClient\Tests\TestCase;
use Saloon\Config;

// Set token early to prevent CLI calls during bootstrap
putenv('GITHUB_TOKEN=test-token-for-testing');
Config::preventStrayRequests();

uses(TestCase::class)
    ->beforeEach(function () {
        // Fake all Process calls - no external CLI in tests
        Process::fake();
        Http::preventStrayRequests();
    })
    ->in('*.php');
