<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\ViewModel;

use Contao\CoreBundle\Image\Studio\Figure;

final readonly class GalleryFolderViewModel
{
    /**
     * @param list<GalleryFolderViewModel> $folders
     */
    public function __construct(
        public string $title,
        public string $slug,
        public array $folders,
        public Figure|null $coverFigure,
        public string|null $description,
    ) {
    }
}
