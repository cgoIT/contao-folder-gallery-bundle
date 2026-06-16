<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\ViewModel;

use Contao\CoreBundle\Image\Studio\Figure;

final readonly class GalleryFolderViewModel
{
    /**
     * @param list<GalleryFolderViewModel> $children
     */
    public function __construct(
        public string $title,
        public string $slug,
        public array $children,
        public Figure|null $coverFigure,
        public string|null $description,
    ) {
    }
}
