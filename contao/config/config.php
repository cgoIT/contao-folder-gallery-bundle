<?php

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCachePurgeTask;
use Cgoit\ContaoFolderGalleryBundle\Security\GalleryBackendAccess;

$GLOBALS['TL_PURGE']['custom']['purgeFolderGalleryCache'] = [
    'callback' => [GalleryCachePurgeTask::class, '__invoke'],
];

// Only registered so that the module can be granted in the user groups; the navigation entry is
// created by the BackendFolderGalleryListener, because the editor is a Symfony route.
$GLOBALS['BE_MOD']['content'][GalleryBackendAccess::MODULE] = [
    'hideInNavigation' => true,
];
