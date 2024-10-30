<?php

namespace JordanPartridge\GithubClient\Commands;

use Illuminate\Console\Command;

class GithubClientCommand extends Command
{
    public $signature = 'github-client';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
