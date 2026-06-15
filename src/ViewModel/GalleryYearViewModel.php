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

final readonly class GalleryYearViewModel
{
    /**
     * @param list<GalleryDayViewModel> $days
     */
    public function __construct(
        public string $title,
        public string $slug,
        public array $days,
    ) {
    }
}
