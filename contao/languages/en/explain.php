<?php

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_LANG']['XPL']['folderGalleryOverviewMode'] = [
    ['Gallery', 'The folder is displayed as a standard gallery.'],
    ['Group', 'The folder serves as a gallery group. To this end, the folder\'s title is displayed as a simple heading in the front end. The subfolders it contains are displayed below it as individual galleries.'],
    ['Transparent', 'The folder is skipped in the gallery structure. Its subfolders are moved directly to the parent level. This is useful, for example, for temporary folders used purely for organizational purposes.'],
];

$GLOBALS['TL_LANG']['XPL']['folderGalleryHideCoverInGallery'] = [
    ['Disabled (default)', 'The cover image is displayed both as a thumbnail in the gallery and within the gallery itself.'],
    ['Enabled', 'The selected cover image is used exclusively as a preview image for the gallery and is not displayed within the gallery itself. This allows a folder to serve as a gallery with its own cover image while also containing additional sub-galleries.'],
    ['Typical Use Case', 'A folder contains only a cover image and additional subgaleries. The cover image appears in the gallery overview, but when the gallery is opened, only the subgaleries are displayed.'],
];
