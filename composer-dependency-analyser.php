<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();
$config
    ->ignoreErrorsOnPackage('phpunit/phpunit', [ErrorType::DEV_DEPENDENCY_IN_PROD]);

return $config;
