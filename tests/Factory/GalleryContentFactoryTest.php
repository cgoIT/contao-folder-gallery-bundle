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

use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryContentFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFigureFactoryInterface;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFolderViewModelFactory;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryContentViewModel;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\ImageResult;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;

#[CoversClass(GalleryContentFactory::class)]
#[UsesClass(GalleryContentViewModel::class)]
#[UsesClass(GalleryFolder::class)]
#[UsesClass(GalleryImage::class)]
#[UsesClass(GalleryMetadata::class)]
#[UsesClass(GalleryFolderViewModel::class)]
final class GalleryContentFactoryTest extends ContaoTestCase
{
    public function testCreatesContentViewModel(): void
    {
        $container = $this->createStub(ContainerInterface::class);

        $childFolder = new GalleryFolder(
            slug: 'child',
            title: 'Child Folder',
            filesystemDirectory: '/files/gallery/parent/child',
            trail: ['parent', 'child'],
            metadata: new GalleryMetadata(),
        );

        $imageA = new GalleryImage(
            uuid: 'uuid-a',
            path: '/gallery/image-a.jpg',
            filename: 'image-a.jpg',
            isCover: true,
        );
        $figureA = new Figure(new ImageResult($container, 'project-dir', 'imageA.jpg'));

        $imageB = new GalleryImage(
            uuid: 'uuid-b',
            path: '/gallery/image-b.jpg',
            filename: 'image-b.jpg',
            isCover: false,
        );
        $figureB = new Figure(new ImageResult($container, 'project-dir', 'imageB.jpg'));

        $folder = new GalleryFolder(
            slug: 'parent',
            title: 'Parent Folder',
            filesystemDirectory: '/files/gallery/parent',
            trail: ['parent'],
            metadata: new GalleryMetadata(),
            folders: [$childFolder],
            images: [$imageA, $imageB],
        );

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->atMost(3))
            ->method('create')
            ->willReturnMap([
                [$imageA, 'image-size', $figureA],
                [$imageB, 'image-size', $figureB],
            ])
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                '/gallery/parent',
                '/gallery/parent/child',
            )
        ;

        $page = $this->createStub(PageModel::class);

        $folderViewModelFactory = new GalleryFolderViewModelFactory($figureFactory, $urlGenerator);

        $factory = new GalleryContentFactory($figureFactory, $folderViewModelFactory);

        $result = $factory->create(
            $folder,
            $page,
            'image-size',
            'cover-size',
        );

        $this->assertSame('Parent Folder', $result->folder->title);
        $this->assertSame('/gallery/parent', $result->folder->url);
        $this->assertCount(1, $result->children);
        $this->assertSame('Child Folder', $result->children[0]->title);
        $this->assertCount(2, $result->images);
        $this->assertSame($figureA, $result->images[0]);
        $this->assertSame($figureB, $result->images[1]);
        $this->assertSame([], $result->breadcrumbs);
    }
}
