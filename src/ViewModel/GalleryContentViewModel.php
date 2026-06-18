<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\ViewModel;

use Contao\CoreBundle\Image\Studio\Figure;

final readonly class GalleryContentViewModel
{
    /**
     * @param list<GalleryFolderViewModel> $folders
     * @param list<Figure>                 $images
     * @param list<mixed>                  $breadcrumbs
     */
    public function __construct(
        public string $title,
        public string|null $description,
        public array $folders,
        public array $images,
        public array $breadcrumbs,
    ) {
    }
}
