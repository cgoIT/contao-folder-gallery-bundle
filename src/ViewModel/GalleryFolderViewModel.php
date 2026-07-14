<?php

declare(strict_types=1);

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
        public int $folderCount,
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
