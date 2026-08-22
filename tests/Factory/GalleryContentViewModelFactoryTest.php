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

use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentAction;
use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentActionInterface;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryBreadcrumbFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryContentViewModelFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFigureFactoryInterface;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFolderViewModelFactory;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryContentActionProvider;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryContentViewModel;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\ImageResult;
use Contao\Image\PictureConfiguration;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(GalleryContentViewModelFactory::class)]
#[UsesClass(GalleryContentActionProvider::class)]
#[UsesClass(GalleryContentViewModel::class)]
#[UsesClass(GalleryOverview::class)]
#[UsesClass(GalleryFolder::class)]
#[UsesClass(GalleryImage::class)]
#[UsesClass(GalleryMetadata::class)]
#[UsesClass(GalleryFolderViewModel::class)]
final class GalleryContentViewModelFactoryTest extends ContaoTestCase
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

        $overview = new GalleryOverview(
            root: new GalleryRoot('folderGallery', 1, '/files/gallery'),
            folders: [$folder],
            folderIndex: ['parent' => $folder],
        );

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->atMost(3))
            ->method('create')
            ->willReturnCallback(
                function ($image, PictureConfiguration|array|int|string|null $size, $viewer, string|null $group) use ($imageA, $imageB, $figureA, $figureB): Figure|null {
                    $this->assertIsString($size);
                    $this->assertSame(GalleryViewer::None, $viewer);

                    return match ($image) {
                        $imageA => $figureA,
                        $imageB => $figureB,
                        default => null,
                    };
                },
            )
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(4))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                '/gallery',
                '/gallery/parent',
                '/gallery/parent',
                '/gallery/parent/child',
            )
        ;

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturn('The alt text')
        ;

        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $model = $this->createStub(ModuleModel::class);
        $model
            ->method('__get')
            ->willReturnMap([
                ['galleryImageSize', 'image-size'],
                ['galleryCoverImageSize', 'cover-size'],
                ['showEmptyGalleryMessage', true],
                ['emptyGalleryMessage', 'This is the empty message'],
            ])
        ;

        $folderViewModelFactory = new GalleryFolderViewModelFactory($figureFactory, $urlGenerator, $translator);

        $galleryBreadcrumbFactory = new GalleryBreadcrumbFactory($urlGenerator);

        $action = new GalleryContentAction(
            type: 'download',
            label: 'Download',
            url: '/gallery/download',
        );

        $actionImplementation = $this->createMock(GalleryContentActionInterface::class);
        $actionImplementation
            ->method('createAction')
            ->with($overview, $folder, $page)
            ->willReturn($action)
        ;

        $actionsProvider = new GalleryContentActionProvider([$actionImplementation]);

        $factory = new GalleryContentViewModelFactory($figureFactory, $folderViewModelFactory, $galleryBreadcrumbFactory, $actionsProvider);

        $result = $factory->create(
            $overview,
            $folder,
            $page,
            $model,
        );

        $this->assertFalse($result->showEmptyMessage);
        $this->assertSame('This is the empty message', $result->emptyMessage);
        $this->assertSame('Parent Folder', $result->folder->title);
        $this->assertSame('/gallery/parent', $result->folder->url);
        $this->assertCount(1, $result->folder->children);
        $this->assertSame('Child Folder', $result->folder->children[0]->title);
        $this->assertCount(2, $result->images);
        $this->assertSame($figureA, $result->images[0]);
        $this->assertSame($figureB, $result->images[1]);
        $this->assertCount(2, $result->breadcrumbs);
        $this->assertCount(1, $result->actions);
        $this->assertSame($action, $result->actions[0]);
    }

    public function testExcludesCoverImageFromGalleryContentIfConfigured(): void
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
        $figureA = new Figure(new ImageResult($container, 'project-dir', 'image-a.jpg'));

        $imageB = new GalleryImage(
            uuid: 'uuid-b',
            path: '/gallery/image-b.jpg',
            filename: 'image-b.jpg',
            isCover: false,
        );
        $figureB = new Figure(new ImageResult($container, 'project-dir', 'image-b.jpg'));

        $folder = new GalleryFolder(
            slug: 'parent',
            title: 'Parent Folder',
            filesystemDirectory: '/files/gallery/parent',
            trail: ['parent'],
            metadata: new GalleryMetadata(hideCoverInGallery: true),
            folders: [$childFolder],
            images: [$imageA, $imageB],
        );

        $overview = new GalleryOverview(
            root: new GalleryRoot('folderGallery', 1, '/files/gallery'),
            folders: [$folder],
            folderIndex: ['parent' => $folder],
        );

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->exactly(1))
            ->method('create')
            ->willReturnCallback(
                function ($image, PictureConfiguration|array|int|string|null $size, $viewer, string|null $group) use ($imageA, $imageB, $figureA, $figureB): Figure|null {
                    $this->assertIsString($size);
                    $this->assertSame(GalleryViewer::None, $viewer);

                    return match ($image) {
                        $imageA => $figureA,
                        $imageB => $figureB,
                        default => null,
                    };
                },
            )
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(4))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                '/gallery',
                '/gallery/parent',
                '/gallery/parent',
                '/gallery/parent/child',
            )
        ;

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturn('The alt text')
        ;

        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $model = $this->createStub(ModuleModel::class);
        $model
            ->method('__get')
            ->willReturnMap([
                ['galleryImageSize', 'image-size'],
                ['galleryCoverImageSize', 'cover-size'],
                ['showEmptyGalleryMessage', true],
                ['emptyGalleryMessage', 'This is the empty message'],
            ])
        ;

        $folderViewModelFactory = new GalleryFolderViewModelFactory($figureFactory, $urlGenerator, $translator);

        $galleryBreadcrumbFactory = new GalleryBreadcrumbFactory($urlGenerator);

        $actionsProvider = new GalleryContentActionProvider([]);

        $factory = new GalleryContentViewModelFactory($figureFactory, $folderViewModelFactory, $galleryBreadcrumbFactory, $actionsProvider);

        $result = $factory->create(
            $overview,
            $folder,
            $page,
            $model,
        );

        $this->assertFalse($result->showEmptyMessage);
        $this->assertSame('This is the empty message', $result->emptyMessage);
        $this->assertSame('Parent Folder', $result->folder->title);
        $this->assertSame('/gallery/parent', $result->folder->url);
        $this->assertCount(1, $result->folder->children);
        $this->assertSame('Child Folder', $result->folder->children[0]->title);
        $this->assertCount(1, $result->images);
        $this->assertSame($figureB, $result->images[array_key_first($result->images)]);
        $this->assertCount(2, $result->breadcrumbs);
    }

    public function testShowsEmptyGalleryMessageForEmptyGallery(): void
    {
        $folder = new GalleryFolder(
            slug: 'parent',
            title: 'Parent Folder',
            filesystemDirectory: '/files/gallery/parent',
            trail: ['parent'],
            metadata: new GalleryMetadata(),
            folders: [],
            images: [],
        );

        $overview = new GalleryOverview(
            root: new GalleryRoot('folderGallery', 1, '/files/gallery'),
            folders: [$folder],
            folderIndex: ['parent' => $folder],
        );

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->never())
            ->method('create')
        ;

        $urlGenerator = $this->createStub(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                '/gallery',
                '/gallery/parent',
                '/gallery/parent',
            )
        ;

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturn('The alt text')
        ;

        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $model = $this->createStub(ModuleModel::class);
        $model
            ->method('__get')
            ->willReturnMap([
                ['galleryImageSize', 'image-size'],
                ['galleryCoverImageSize', 'cover-size'],
                ['showEmptyGalleryMessage', true],
                ['emptyGalleryMessage', '<p>Bilder folgen in Kürze.</p>'],
            ])
        ;

        $folderViewModelFactory = new GalleryFolderViewModelFactory($figureFactory, $urlGenerator, $translator);

        $galleryBreadcrumbFactory = new GalleryBreadcrumbFactory($urlGenerator);

        $actionsProvider = new GalleryContentActionProvider([]);

        $factory = new GalleryContentViewModelFactory(
            $figureFactory,
            $folderViewModelFactory,
            $galleryBreadcrumbFactory,
            $actionsProvider,
        );

        $result = $factory->create(
            $overview,
            $folder,
            $page,
            $model,
        );

        $this->assertTrue($result->showEmptyMessage);
        $this->assertSame('<p>Bilder folgen in Kürze.</p>', $result->emptyMessage);
        $this->assertCount(0, $result->images);
        $this->assertCount(0, $result->folder->children);
    }

    public function testShowsEmptyGalleryMessageIfOnlyCoverImageExistsAndHideCoverInGalleryIsEnabled(): void
    {
        $container = $this->createStub(ContainerInterface::class);

        $coverImage = new GalleryImage(
            uuid: 'uuid-cover',
            path: '/files/gallery/cover.jpg',
            filename: 'cover.jpg',
            isCover: true,
        );
        $figureCover = new Figure(new ImageResult($container, 'project-dir', 'cover.jpg'));

        $folder = new GalleryFolder(
            slug: 'parent',
            title: 'Parent Folder',
            filesystemDirectory: '/files/gallery/parent',
            trail: ['parent'],
            metadata: new GalleryMetadata(
                hideCoverInGallery: true,
            ),
            folders: [],
            images: [$coverImage],
        );

        $overview = new GalleryOverview(
            root: new GalleryRoot('folderGallery', 1, '/files/gallery'),
            folders: [$folder],
            folderIndex: ['parent' => $folder],
        );

        $figureFactory = $this->createMock(GalleryFigureFactoryInterface::class);
        $figureFactory
            ->expects($this->never())
            ->method('create')
            ->willReturn($figureCover)
        ;

        $urlGenerator = $this->createStub(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturn('/gallery')
        ;

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturn('The alt text')
        ;

        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $model = $this->createStub(ModuleModel::class);
        $model
            ->method('__get')
            ->willReturnMap([
                ['galleryImageSize', 'image-size'],
                ['galleryCoverImageSize', 'cover-size'],
                ['showEmptyGalleryMessage', true],
                ['emptyGalleryMessage', '<p>Bilder folgen in Kürze.</p>'],
            ])
        ;

        $factory = new GalleryContentViewModelFactory(
            $figureFactory,
            new GalleryFolderViewModelFactory($figureFactory, $urlGenerator, $translator),
            new GalleryBreadcrumbFactory($urlGenerator),
            new GalleryContentActionProvider([]),
        );

        $result = $factory->create(
            $overview,
            $folder,
            $page,
            $model,
        );

        $this->assertCount(0, $result->images);
        $this->assertTrue($result->showEmptyMessage);
        $this->assertSame(
            '<p>Bilder folgen in Kürze.</p>',
            $result->emptyMessage,
        );
    }
}
