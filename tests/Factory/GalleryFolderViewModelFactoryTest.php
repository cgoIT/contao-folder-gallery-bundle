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
use Contao\CoreBundle\Image\Studio\Figure;
use PHPUnit\Framework\TestCase;

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

        $child = new GalleryFolder(
            slug: 'child',
            title: 'Child',
            trail: ['parent', 'child'],
            description: 'Child description',
            publishedFrom: null,
            publishedUntil: null,
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
            trail: ['parent'],
            description: 'Parent description',
            publishedFrom: null,
            publishedUntil: null,
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

        $factory = new GalleryFolderViewModelFactory($figureFactory);

        $viewModel = $factory->create($parent, null);

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

        $child = new GalleryFolder(
            slug: 'child',
            title: 'Child',
            trail: ['parent', 'child'],
            description: null,
            publishedFrom: null,
            publishedUntil: null,
            folders: [],
            images: [],
        );

        $parent = new GalleryFolder(
            slug: 'parent',
            title: 'Parent',
            trail: ['parent'],
            description: null,
            publishedFrom: null,
            publishedUntil: null,
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

        $factory = new GalleryFolderViewModelFactory($figureFactory);

        $viewModel = $factory->create(
            $parent,
            null,
            false,
        );

        $this->assertCount(0, $viewModel->children);
    }
}
