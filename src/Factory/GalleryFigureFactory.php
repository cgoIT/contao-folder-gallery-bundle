<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\Image\PictureConfiguration;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(GalleryFigureFactoryInterface::class)]
final readonly class GalleryFigureFactory implements GalleryFigureFactoryInterface
{
    public function __construct(private Studio $studio)
    {
    }

    /**
     * @param PictureConfiguration|array<mixed>|int|string|null $size
     */
    public function create(GalleryImage $image, PictureConfiguration|array|int|string|null $size): Figure
    {
        return $this->studio
            ->createFigureBuilder()
            ->fromUuid($image->uuid)
            ->setSize($size)
            ->build()
        ;
    }
}
