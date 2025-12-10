<?php

use Illuminate\Support\Facades\Process;
use JordanPartridge\GithubClient\Tests\TestCase;

// Set token early to prevent CLI calls during bootstrap
putenv('GITHUB_TOKEN=test-token-for-testing');

uses(TestCase::class)
    ->beforeEach(function () {
        // Fake all Process calls - no external CLI in tests
        Process::fake();
    })
    ->in(__DIR__);
