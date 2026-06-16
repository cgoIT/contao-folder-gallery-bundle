<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryOverviewViewModel;
use Contao\Image\PictureConfiguration;

final readonly class GalleryOverviewFactory
{
    public function __construct(private GalleryFigureFactoryInterface $figureFactory)
    {
    }

    /**
     * @param array<mixed>|PictureConfiguration|int|string|null $coverImageSize
     */
    public function create(GalleryOverview $overview, PictureConfiguration|array|int|string|null $coverImageSize): GalleryOverviewViewModel
    {
        $folders = array_map(
            fn (GalleryFolder $folder) => $this->toGalleryFolderViewModel($folder, $coverImageSize),
            $overview->folders,
        );

        return new GalleryOverviewViewModel(
            folders: $folders,
        );
    }

    /**
     * @param array<mixed>|PictureConfiguration|int|string|null $coverImageSize
     */
    private function toGalleryFolderViewModel(GalleryFolder $folder, PictureConfiguration|array|int|string|null $coverImageSize): GalleryFolderViewModel
    {
        $coverImage = $folder->getCoverImage();

        return new GalleryFolderViewModel(
            title: $folder->title,
            slug: $folder->slug,
            children: array_map(
                fn (GalleryFolder $subFolder) => $this->toGalleryFolderViewModel($subFolder, $coverImageSize),
                $folder->folders,
            ),
            coverFigure: $coverImage
                ? $this->figureFactory->create($coverImage, $coverImageSize)
                : null,
            description: $folder->description,
        );
    }
}
