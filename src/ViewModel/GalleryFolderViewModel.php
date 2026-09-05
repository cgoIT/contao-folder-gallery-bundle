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

use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Contao\CoreBundle\Image\Studio\Figure;

final readonly class GalleryFolderViewModel
{
    /**
     * @param list<GalleryFolderViewModel> $children
     */
    public function __construct(
        public string $title,
        public string $slug,
        public string $url,
        public array $children,
        public int $imageCount,
        public int $galleryCount,
        public Figure|null $coverFigure,
        public string|null $description,
        public string|null $anchor,
        public int $level,
        public OverviewMode $overviewMode,
    ) {
    }

    public function isGroup(): bool
    {
        return OverviewMode::Group === $this->overviewMode;
    }

    public function isGallery(): bool
    {
        return OverviewMode::Gallery === $this->overviewMode;
    }
}
