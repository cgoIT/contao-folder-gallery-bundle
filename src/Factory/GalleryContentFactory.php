<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryContentViewModel;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\Image\PictureConfiguration;

final readonly class GalleryContentFactory
{
    public function __construct(
        private GalleryFigureFactoryInterface $figureFactory,
        private GalleryFolderViewModelFactory $folderViewModelFactory,
    ) {
    }

    /**
     * @param array<mixed>|PictureConfiguration|int|string|null $imageSize
     * @param array<mixed>|PictureConfiguration|int|string|null $coverImageSize
     */
    public function create(GalleryFolder $folder, PictureConfiguration|array|int|string|null $imageSize, PictureConfiguration|array|int|string|null $coverImageSize): GalleryContentViewModel
    {
        return new GalleryContentViewModel(
            title: $folder->title,
            description: $folder->description,
            folders: array_map(
                fn (GalleryFolder $child) => $this->folderViewModelFactory->create($child, $coverImageSize, false),
                $folder->folders,
            ),
            images: array_map(
                fn (GalleryImage $image): Figure => $this->figureFactory->create(
                    $image,
                    $imageSize,
                ),
                $folder->images,
            ),
            breadcrumbs: [],
        );
    }
}
