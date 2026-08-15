<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\ViewModel;

use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentAction;
use Contao\CoreBundle\Image\Studio\Figure;

final readonly class GalleryContentViewModel
{
    /**
     * @param list<Figure>                     $images
     * @param list<GalleryContentAction>       $actions
     * @param list<GalleryBreadcrumbViewModel> $breadcrumbs
     */
    public function __construct(
        public GalleryFolderViewModel $folder,
        public array $images,
        public array $actions,
        public bool $showEmptyMessage,
        public string|null $emptyMessage,
        public array $breadcrumbs,
        public string|null $backUrl,
    ) {
    }
}
