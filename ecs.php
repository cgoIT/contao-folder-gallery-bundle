<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'header' => <<<EOF
This file is part of cgoit\\contao-folder-gallery-bundle for Contao Open Source CMS.

@copyright  Copyright (c) cgoIT
@author     cgoIT <https://cgo-it.de>
@license    LGPL-3.0-or-later
EOF
        ,
    ]);
};
