<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Class_\RenameAttributeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->skip([
        \Contao\Rector\Rector\LegacyFrameworkCallToServiceCallRector::class => [
            'tests/EventListener/DataContainer/ModuleCallbacksTest.php'
        ],
    ]);
};
