<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\EventListener\DataContainer;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\EventListener\DataContainer\FilesCallbacks;
use Cgoit\ContaoFolderGalleryBundle\Matcher\GalleryPathMatcher;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilesCallbacks::class)]
#[UsesClass(PaletteManipulator::class)]
#[UsesClass(GalleryPathMatcher::class)]
#[UsesClass(GalleryRoot::class)]
final class FilesCallbacksTest extends TestCase
{
    public function testAddsFieldForImageFiles(): void
    {
        $dc = $this->createStub(DataContainer::class);
        $dc
            ->method('__get')
            ->willReturn('files/gallery/image.jpg')
        ;

        $callbacks = $this->createFilesCallbacks();

        $palette = $callbacks->addHideInGalleryField('name,importantPartX,importantPartHeight', $dc);

        $this->assertStringContainsString('hideInGallery', $palette);
    }

    public function testDoesNotAddFieldForNonImageFiles(): void
    {
        $dc = $this->createStub(DataContainer::class);
        $dc
            ->method('__get')
            ->willReturn('files/gallery/document.pdf')
        ;

        $callbacks = $this->createFilesCallbacks();

        $palette = 'name,importantPartX,importantPartHeight';

        $this->assertSame($palette, $callbacks->addHideInGalleryField($palette, $dc));
    }

    public function testInvalidatesGalleryCacheForFileInsideGalleryRoot(): void
    {
        $dc = $this->createStub(DataContainer::class);
        $dc
            ->method('__get')
            ->willReturn('files/gallery/image.jpg')
        ;

        $galleryCacheInvalidator = $this->createMock(GalleryCacheInvalidator::class);
        $galleryCacheInvalidator
            ->expects($this->once())
            ->method('invalidate')
        ;

        $callbacks = $this->createFilesCallbacks(
            galleryRoots: [new GalleryRoot('module', 1, 'files/gallery')],
            galleryCacheInvalidator: $galleryCacheInvalidator,
        );

        $callbacks->invalidateGalleryCacheOnSave($dc);
    }

    public function testDoesNotInvalidateGalleryCacheForFileOutsideGalleryRoot(): void
    {
        $dc = $this->createStub(DataContainer::class);
        $dc
            ->method('__get')
            ->willReturn('files/downloads/document.pdf')
        ;

        $galleryCacheInvalidator = $this->createMock(GalleryCacheInvalidator::class);
        $galleryCacheInvalidator
            ->expects($this->never())
            ->method('invalidate')
        ;

        $callbacks = $this->createFilesCallbacks(
            galleryRoots: [new GalleryRoot('module', 1, 'files/gallery')],
            galleryCacheInvalidator: $galleryCacheInvalidator,
        );

        $callbacks->invalidateGalleryCacheOnSave($dc);
    }

    /**
     * @param list<GalleryRoot> $galleryRoots
     */
    private function createFilesCallbacks(array $galleryRoots = [], GalleryCacheInvalidator|null $galleryCacheInvalidator = null): FilesCallbacks
    {
        $rootProvider = $this->createStub(GalleryRootProviderInterface::class);
        $rootProvider
            ->method('getGalleryRoots')
            ->willReturn($galleryRoots)
        ;

        return new FilesCallbacks(
            ['jpg', 'png'],
            new GalleryPathMatcher($rootProvider),
            $galleryCacheInvalidator ?? $this->createStub(GalleryCacheInvalidator::class),
        );
    }
}
