<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_files']['fields']['hideInGallery'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr w50 m12'],
    'sql' => ['type' => 'boolean', 'default' => false],
];
