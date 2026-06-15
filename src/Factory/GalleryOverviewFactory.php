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

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryDay;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryYear;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryDayViewModel;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryOverviewViewModel;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryYearViewModel;
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
        $years = array_map(
            fn (GalleryYear $year) => $this->toGalleryYearViewModel($year, $coverImageSize),
            $overview->years,
        );

        return new GalleryOverviewViewModel(
            years: $years,
        );
    }

    /**
     * @param array<mixed>|PictureConfiguration|int|string|null $coverImageSize
     */
    private function toGalleryYearViewModel(GalleryYear $year, PictureConfiguration|array|int|string|null $coverImageSize): GalleryYearViewModel
    {
        return new GalleryYearViewModel(
            title: $year->title,
            slug: $year->slug,
            days: array_map(
                fn (GalleryDay $day) => $this->toGalleryDayViewModel($day, $coverImageSize),
                $year->days,
            ),
        );
    }

    /**
     * @param array<mixed>|PictureConfiguration|int|string|null $coverImageSize
     */
    private function toGalleryDayViewModel(GalleryDay $day, PictureConfiguration|array|int|string|null $coverImageSize): GalleryDayViewModel
    {
        $coverImage = $day->getCoverImage();

        return new GalleryDayViewModel(
            title: $day->title,
            slug: $day->slug,
            coverFigure: $coverImage
                ? $this->figureFactory->create($coverImage, $coverImageSize)
                : null,
            description: $day->description,
        );
    }
}
