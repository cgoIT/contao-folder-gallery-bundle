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
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\PageModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryFolderViewModelFactory::class)]
#[UsesClass(GalleryFolder::class)]
#[UsesClass(GalleryImage::class)]
final class GalleryFolderViewModelFactoryTest extends TestCase
{
    public function testCreatesFolderViewModelRecursively(): void
    {
        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->exactly(2))
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

        $child = new GalleryFolder(
            slug: 'child',
            title: 'Child',
            filesystemDirectory: '/files/gallery/parent/child',
            trail: ['parent', 'child'],
            metadata: new GalleryMetadata(description: 'Child description'),
            folders: [],
            images: [
                new GalleryImage(
                    uuid: 'child',
                    path: '/',
                    filename: 'child.jpg',
                    isCover: true,
                ),
            ],
        );

        $parent = new GalleryFolder(
            slug: 'parent',
            title: 'Parent',
            filesystemDirectory: '/files/gallery/parent',
            trail: ['parent'],
            metadata: new GalleryMetadata(description: 'Parent description'),
            folders: [$child],
            images: [
                new GalleryImage(
                    uuid: 'parent',
                    path: '/',
                    filename: 'parent.jpg',
                    isCover: true,
                ),
            ],
        );

        $factory = new GalleryFolderViewModelFactory($figureFactory, $urlGenerator);

        $viewModel = $factory->create($parent, $pageModel, null);

        $this->assertSame('Parent', $viewModel->title);
        $this->assertSame('parent', $viewModel->slug);
        $this->assertSame('Parent description', $viewModel->description);
        $this->assertNotInstanceOf(Figure::class, $viewModel->coverFigure);

        $this->assertCount(1, $viewModel->children);

        $childViewModel = $viewModel->children[0];

        $this->assertSame('Child', $childViewModel->title);
        $this->assertSame('child', $childViewModel->slug);
        $this->assertSame('Child description', $childViewModel->description);
        $this->assertNotInstanceOf(Figure::class, $childViewModel->coverFigure);
    }

    public function testCanCreateFolderViewModelWithoutChildren(): void
    {
        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn(null)
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/parent/child')
        ;

        $pageModel = $this->createStub(PageModel::class);

        $child = new GalleryFolder(
            slug: 'child',
            title: 'Child',
            filesystemDirectory: '/files/gallery/parent/child',
            trail: ['parent', 'child'],
            metadata: new GalleryMetadata(),
            folders: [],
            images: [],
        );

        $parent = new GalleryFolder(
            slug: 'parent',
            title: 'Parent',
            filesystemDirectory: '/files/gallery/parent',
            trail: ['parent'],
            metadata: new GalleryMetadata(),
            folders: [$child],
            images: [
                new GalleryImage(
                    uuid: 'parent',
                    path: '/',
                    filename: 'parent.jpg',
                    isCover: true,
                ),
            ],
        );

        $factory = new GalleryFolderViewModelFactory($figureFactory, $urlGenerator);

        $viewModel = $factory->create(
            $parent,
            $pageModel,
            null,
            false,
        );

        $this->assertCount(0, $viewModel->children);
    }
}
