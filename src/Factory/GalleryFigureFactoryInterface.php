<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\Image\PictureConfiguration;

interface GalleryFigureFactoryInterface
{
    /**
     * @param PictureConfiguration|array<mixed>|int|string|null $size
     */
    public function create(GalleryImage $image, PictureConfiguration|array|int|string|null $size): Figure|null;
}
