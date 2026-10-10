<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_LANG']['tl_module']['viewer_legend'] = 'Viewer settings';

$GLOBALS['TL_LANG']['tl_module']['galleryRoot'] = ['Gallery root folder', 'Select the folder that contains the folders of your gallery.'];
$GLOBALS['TL_LANG']['tl_module']['galleryCoverImageSize'] = ['Gallery cover size', 'Select the size of the gallery cover images.'];
$GLOBALS['TL_LANG']['tl_module']['galleryImageSize'] = ['Gallery image size', 'Select the size of the thumbnails in a gallery.'];
$GLOBALS['TL_LANG']['tl_module']['galleryOverviewMessage'] = ['Text on the gallery overview', 'Text that appears on the gallery overview page in the front end. By default, this text appears above the galleries.'];
$GLOBALS['TL_LANG']['tl_module']['showEmptyGalleryMessage'] = ['Show message for empty galleries', 'Displays a message if a published gallery contains neither visible sub-galleries nor visible images.'];
$GLOBALS['TL_LANG']['tl_module']['emptyGalleryMessage'] = ['Message for empty galleries', 'This message is displayed if the gallery is published but contains neither visible sub-galleries nor visible images.'];
$GLOBALS['TL_LANG']['tl_module']['galleryContentTpl'] = ['Content template', 'Template to use for the contents of a gallery'];
$GLOBALS['TL_LANG']['tl_module']['galleryFolderTpl'] = ['Folder template', 'Template for the folders of a gallery'];
$GLOBALS['TL_LANG']['tl_module']['galleryDisableStylesheet'] = ['Do not load default stylesheet', 'The stylesheet of the bundle is not included. Useful if the presentation is fully handled by custom templates and styles. The PhotoSwipe stylesheet is not affected.'];
$GLOBALS['TL_LANG']['tl_module']['galleryViewer'] = ['Image viewer', 'Select the viewer to be used for displaying individual images in full-screen view within a gallery.'];
$GLOBALS['TL_LANG']['tl_module']['galleryLightboxSize'] = ['Lightbox image size', 'Image size of the large view in the image viewer. If empty, the lightbox image size of the page layout is used.'];
$GLOBALS['TL_LANG']['tl_module']['galleryLightboxSizeMissing'] = 'The module does not define a lightbox image size. Please make sure it is set in all page layouts the module is used in. Otherwise the large view links to the original image. It cannot be accessed in protected folders and contains all EXIF data (e.g. GPS coordinates) in public folders.';

$GLOBALS['TL_LANG']['tl_module']['gallery_viewer']['none'] = 'No viewer';
$GLOBALS['TL_LANG']['tl_module']['gallery_viewer']['lightbox'] = 'Lightbox (Contao Default)';
$GLOBALS['TL_LANG']['tl_module']['gallery_viewer']['photoswipe'] = 'PhotoSwipe';
