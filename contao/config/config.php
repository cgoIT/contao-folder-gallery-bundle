<?php

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCachePurgeTask;

$GLOBALS['TL_PURGE']['custom']['purgeFolderGalleryCache'] = [
    'callback' => [GalleryCachePurgeTask::class, '__invoke'],
];
