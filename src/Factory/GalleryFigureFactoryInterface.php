<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\Image\PictureConfiguration;

interface GalleryFigureFactoryInterface
{
    /**
     * @param PictureConfiguration|array<mixed>|int|string|null $size
     */
    public function create(GalleryImage $image, PictureConfiguration|array|int|string|null $size, GalleryViewer $galleryViewer = GalleryViewer::None, string|null $lightboxGroupIdentifier = null): Figure|null;

    /**
     * @param PictureConfiguration|array<mixed>|int|string|null $size
     */
    public function createCoverImage(GalleryImage $image, PictureConfiguration|array|int|string|null $size, string $folderUrl, string $alt): Figure|null;
}
