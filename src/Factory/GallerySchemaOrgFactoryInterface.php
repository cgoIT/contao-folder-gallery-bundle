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

use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryBreadcrumbViewModel;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\CoreBundle\Image\Studio\Figure;

interface GallerySchemaOrgFactoryInterface
{
    /**
     * @param list<Figure> $images
     *
     * @return array<string, mixed>
     */
    public function create(GalleryFolderViewModel $folder, array $images): array;

    /**
     * @param list<GalleryBreadcrumbViewModel> $breadcrumbs
     *
     * @return array<string, mixed>
     */
    public function createBreadcrumbList(array $breadcrumbs): array;
}
