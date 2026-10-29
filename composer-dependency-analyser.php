<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

// orchestra/testbench installs laravel/framework, which replaces illuminate/*.
// Class files from those components are then attributed to laravel/framework,
// and the illuminate packages look unused.
return (new Configuration())
    ->ignoreErrorsOnPackage('laravel/framework', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackages(
        [
            'illuminate/collections',
            'illuminate/console',
            'illuminate/http',
            'illuminate/process',
            'illuminate/support',
        ],
        [ErrorType::UNUSED_DEPENDENCY],
    );
