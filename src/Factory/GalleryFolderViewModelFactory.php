<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\Image\PictureConfiguration;

final readonly class GalleryFolderViewModelFactory
{
    public function __construct(private GalleryFigureFactoryInterface $figureFactory)
    {
    }

    /**
     * @param PictureConfiguration|array<mixed>|int|string|null $coverImageSize
     */
    public function create(GalleryFolder $folder, PictureConfiguration|array|int|string|null $coverImageSize, bool $recursive = true): GalleryFolderViewModel
    {
        $coverImage = $folder->getCoverImage();

        return new GalleryFolderViewModel(
            title: $folder->title,
            slug: $folder->slug,
            children: $recursive
                ? array_map(
                    fn (GalleryFolder $child) => $this->create(
                        $child,
                        $coverImageSize,
                        true,
                    ),
                    $folder->folders,
                )
                : [],
            coverFigure: $coverImage
                ? $this->figureFactory->create($coverImage, $coverImageSize)
                : null,
            description: $folder->description,
        );
    }
}
