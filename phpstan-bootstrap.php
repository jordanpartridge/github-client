<?php

// Set dummy environment variables for PHPStan analysis
if (! getenv('GITHUB_TOKEN')) {
    putenv('GITHUB_TOKEN=dummy-token-for-static-analysis');
}
