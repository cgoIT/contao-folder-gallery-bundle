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
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFolderViewModelFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryOverviewFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\OverviewFolderFlattener;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\PageModel;
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

        $day = new GalleryFolder(
            slug: 'friday',
            title: 'Friday',
            trail: ['2025', 'friday'],
            metadata: new GalleryMetadata(description: 'Friday description'),
            images: [$image],
        );

        $year = new GalleryFolder(
            slug: 'year-2025',
            title: 'Year 2025',
            trail: ['year-2025'],
            metadata: new GalleryMetadata(),
            folders: [$day],
        );

        $overview = new GalleryOverview([$year], ['year-2025' => $year, 'year-2025/friday' => $day]);

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn(null)
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturn('/parent/child')
        ;

        $pageModel = $this->createStub(PageModel::class);

        $galleryFolderFactory = new GalleryFolderViewModelFactory($figureFactory, $urlGenerator);

        $factory = new GalleryOverviewFactory($galleryFolderFactory, new OverviewFolderFlattener());

        $viewModel = $factory->create($overview, $pageModel, 'gallery_cover');

        $this->assertCount(1, $viewModel->folders);

        $yearViewModel = $viewModel->folders[0];

        $this->assertSame('Year 2025', $yearViewModel->title);
        $this->assertSame('year-2025', $yearViewModel->slug);
        $this->assertCount(1, $yearViewModel->children);

        $dayViewModel = $yearViewModel->children[0];

        $this->assertSame('Friday', $dayViewModel->title);
        $this->assertSame('friday', $dayViewModel->slug);
        $this->assertSame('Friday description', $dayViewModel->description);
        $this->assertNotInstanceOf(Figure::class, $dayViewModel->coverFigure);
    }

    public function testCreatesDayWithoutCoverFigure(): void
    {
        $day = new GalleryFolder(
            slug: 'friday',
            title: 'Friday',
            trail: ['year-2025', 'friday'],
            metadata: new GalleryMetadata(description: 'Friday description'),
            images: [],
        );

        $year = new GalleryFolder(
            slug: 'year-2025',
            title: 'Year 20425',
            trail: ['year-2025'],
            metadata: new GalleryMetadata(description: 'Friday description'),
            folders: [$day],
        );

        $overview = new GalleryOverview([$year], ['year-2025' => $year, 'year-2025/friday' => $day]);

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->never())
            ->method('create')
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturn('/parent/child')
        ;

        $pageModel = $this->createStub(PageModel::class);

        $galleryFolderFactory = new GalleryFolderViewModelFactory($figureFactory, $urlGenerator);

        $factory = new GalleryOverviewFactory($galleryFolderFactory, new OverviewFolderFlattener());

        $viewModel = $factory->create($overview, $pageModel, 'gallery_cover');

        $dayViewModel = $viewModel->folders[0]->children[0];

        $this->assertNotInstanceOf(Figure::class, $dayViewModel->coverFigure);
    }
}
