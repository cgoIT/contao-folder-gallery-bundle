<?php

declare(strict_types=1);

$configFile = __DIR__.'/../vendor/terminal42/contao-build-tools/tools/code-quality-tools/vendor/terminal42/code-quality-tools/tools/rector/config.php';

if (!is_file($configFile)) {
    fwrite(STDERR, sprintf(
        "Rector config not found: %s\n",
        $configFile,
    ));

    exit(0);
}

$content = file_get_contents($configFile);

if (false === $content) {
    throw new RuntimeException(sprintf(
        'Unable to read Rector config: %s',
        $configFile,
    ));
}

$old = <<<'PHP'
    if ($phpunitConstraint = $composerJson->requirement('phpunit/phpunit')) {
        $lowerBound = $versionParser->parseConstraints($phpunitConstraint)->getLowerBound();

        $setList = [
            '>= 4.0' => [PHPUnitSetList::PHPUNIT_40],
            '>= 5.0' => [PHPUnitSetList::PHPUNIT_50],
            '>= 6.0' => [PHPUnitSetList::PHPUNIT_60],
            '>= 7.0' => [PHPUnitSetList::PHPUNIT_70],
            '>= 8.0' => [PHPUnitSetList::PHPUNIT_80],
            '>= 9.0' => [PHPUnitSetList::PHPUNIT_90],
            '>= 10.0' => [PHPUnitSetList::PHPUNIT_100, PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES],
            '>= 11.0' => [PHPUnitSetList::PHPUNIT_110],
            '>= 12.0' => [PHPUnitSetList::PHPUNIT_120],
        ];

        $setList = array_filter(
            $setList,
            static fn ($constraint) => $lowerBound->compareTo($versionParser->parseConstraints($constraint)->getLowerBound(), '>'),
            ARRAY_FILTER_USE_KEY,
        );

        if (!empty($setList)) {
            $rectorConfig->sets(array_merge([PHPUnitSetList::PHPUNIT_CODE_QUALITY], ...array_values($setList)));
        }
    }
PHP;

$new = <<<'PHP'
    if ($composerJson->requirement('phpunit/phpunit')) {
        $rectorConfig->sets([PHPUnitSetList::PHPUNIT_CODE_QUALITY]);
    }
PHP;

if (!str_contains($content, $old)) {
    if (str_contains($content, 'PHPUnitSetList::PHPUNIT_40')) {
        fwrite(STDERR, "Rector config contains the problematic PHPUnit sets, but the expected block was not found.\n");

        exit(1);
    }

    // Already patched or a different version of the config.
    exit(0);
}

$content = str_replace($old, $new, $content);

if (false === file_put_contents($configFile, $content)) {
    throw new RuntimeException(sprintf(
        'Unable to write Rector config: %s',
        $configFile,
    ));
}

echo "Patched Rector PHPUnit configuration.\n";
