<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Factory;

use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFigureFactoryInterface;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryOverviewFactory;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryDay;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryYear;
use Contao\CoreBundle\Image\Studio\Figure;
use PHPUnit\Framework\TestCase;

final class GalleryOverviewFactoryTest extends TestCase
{
    public function testCreatesViewModel(): void
    {
        $image = new GalleryImage(
            uuid: '12345678-1234-1234-1234-123456789abc',
            path: 'files/gallery/2025/friday/cover.jpg',
            filename: 'cover.jpg',
            isCover: true,
        );

        $day = new GalleryDay(
            year: '2025',
            slug: 'friday',
            title: 'Friday',
            description: 'Friday description',
            publishedFrom: null,
            publishedUntil: null,
            images: [$image],
        );

        $year = new GalleryYear(
            slug: '2025',
            title: 'Year 2025',
            publishedFrom: null,
            publishedUntil: null,
            days: [$day],
        );

        $overview = new GalleryOverview([$year]);

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $image,
                'gallery_cover',
            )
            ->willReturn(null)
        ;

        $factory = new GalleryOverviewFactory($figureFactory);

        $viewModel = $factory->create($overview, 'gallery_cover');

        $this->assertCount(1, $viewModel->years);

        $yearViewModel = $viewModel->years[0];

        $this->assertSame('Year 2025', $yearViewModel->title);
        $this->assertSame('2025', $yearViewModel->slug);
        $this->assertCount(1, $yearViewModel->days);

        $dayViewModel = $yearViewModel->days[0];

        $this->assertSame('Friday', $dayViewModel->title);
        $this->assertSame('friday', $dayViewModel->slug);
        $this->assertSame('Friday description', $dayViewModel->description);
        $this->assertNotInstanceOf(Figure::class, $dayViewModel->coverFigure);
    }

    public function testCreatesDayWithoutCoverFigure(): void
    {
        $day = new GalleryDay(
            year: '2025',
            slug: 'friday',
            title: 'Friday',
            description: null,
            publishedFrom: null,
            publishedUntil: null,
            images: [],
        );

        $year = new GalleryYear(
            slug: '2025',
            title: 'Year 2025',
            publishedFrom: null,
            publishedUntil: null,
            days: [$day],
        );

        $overview = new GalleryOverview([$year]);

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->never())
            ->method('create')
        ;

        $factory = new GalleryOverviewFactory($figureFactory);

        $viewModel = $factory->create($overview, 'gallery_cover');

        $dayViewModel = $viewModel->years[0]->days[0];

        $this->assertNotInstanceOf(Figure::class, $dayViewModel->coverFigure);
    }
}
