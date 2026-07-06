<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryContentViewModel;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\Image\PictureConfiguration;
use Contao\PageModel;

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
    public function create(GalleryFolder $folder, PageModel $page, PictureConfiguration|array|int|string|null $imageSize, PictureConfiguration|array|int|string|null $coverImageSize, GalleryViewer $galleryViewer = GalleryViewer::None): GalleryContentViewModel
    {
        return new GalleryContentViewModel(
            folder: $this->folderViewModelFactory->create($folder, $page, $coverImageSize, false),
            children: array_map(
                fn (GalleryFolder $child) => $this->folderViewModelFactory->create($child, $page, $coverImageSize, false),
                $folder->folders,
            ),
            images: array_map(
                fn (GalleryImage $image): Figure => $this->figureFactory->create(
                    $image,
                    $imageSize,
                    $galleryViewer,
                    'lb-'.$page->id.'-'.$folder->slug,
                ),
                $folder->images,
            ),
            breadcrumbs: [],
        );
    }
}
