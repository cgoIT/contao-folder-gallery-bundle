<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Loader;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;

interface GalleryImageLoaderInterface
{
    /**
     * @return list<GalleryImage>
     */
    public function loadImages(string $directory, string|null $coverImageName): array;
}
