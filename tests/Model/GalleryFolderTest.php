<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Model;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryFolder::class)]
#[UsesClass(GalleryImage::class)]
final class GalleryFolderTest extends TestCase
{
    public function testReturnsCoverImage(): void
    {
        $image1 = new GalleryImage(
            uuid: '1',
            path: '/image1.jpg',
            filename: 'image1.jpg',
            isCover: false,
        );

        $image2 = new GalleryImage(
            uuid: '2',
            path: '/image2.jpg',
            filename: 'image2.jpg',
            isCover: true,
        );

        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(),
            images: [$image1, $image2],
        );

        $this->assertSame($image2, $folder->getCoverImage());
    }

    public function testReturnsFirstImageIfNoCoverImageExists(): void
    {
        $image1 = new GalleryImage(
            uuid: '1',
            path: '/image1.jpg',
            filename: 'image1.jpg',
            isCover: false,
        );

        $image2 = new GalleryImage(
            uuid: '2',
            path: '/image2.jpg',
            filename: 'image2.jpg',
            isCover: false,
        );

        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(),
            images: [$image1, $image2],
        );

        $this->assertSame($image1, $folder->getCoverImage());
    }

    public function testReturnsNullIfFolderHasNoImages(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(),
        );

        $this->assertNotInstanceOf(GalleryImage::class, $folder->getCoverImage());
    }

    public function testReturnsPath(): void
    {
        $folder = new GalleryFolder(
            slug: 'friday',
            title: 'Friday',
            filesystemDirectory: '/files/gallery/friday',
            trail: ['2025', 'friday'],
            metadata: new GalleryMetadata(),
        );

        $this->assertSame('2025/friday', $folder->getPath());
    }

    public function testReturnsDepth(): void
    {
        $folder = new GalleryFolder(
            slug: 'friday',
            title: 'Friday',
            filesystemDirectory: '/files/gallery/friday',
            trail: ['2025', 'friday'],
            metadata: new GalleryMetadata(),
        );

        $this->assertSame(2, $folder->getDepth());
    }

    public function testDetectsSubFolders(): void
    {
        $child = new GalleryFolder(
            slug: 'child',
            title: 'Child',
            filesystemDirectory: '/files/gallery/parent/child',
            trail: ['child'],
            metadata: new GalleryMetadata(),
        );

        $folder = new GalleryFolder(
            slug: 'parent',
            title: 'Parent',
            filesystemDirectory: '/files/gallery/parent',
            trail: ['parent'],
            metadata: new GalleryMetadata(),
            folders: [$child],
        );

        $this->assertTrue($folder->hasSubFolders());
    }

    public function testDetectsMissingSubFolders(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(),
        );

        $this->assertFalse($folder->hasSubFolders());
    }

    public function testDetectsImages(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(),
            images: [
                new GalleryImage(
                    uuid: '1',
                    path: '/image.jpg',
                    filename: 'image.jpg',
                    isCover: false,
                ),
            ],
        );

        $this->assertTrue($folder->hasImages());
    }

    public function testDetectsMissingImages(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(),
        );

        $this->assertFalse($folder->hasImages());
    }

    public function testReturnsDescription(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(
                description: 'Description',
            ),
        );

        $this->assertSame('Description', $folder->getDescription());
    }

    public function testReturnsOverviewMode(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(
                overviewMode: OverviewMode::Group,
            ),
        );

        $this->assertSame(OverviewMode::Group, $folder->getOverviewMode());
    }

    public function testReturnsPublishedState(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(),
        );

        $this->assertTrue($folder->isPublished());
    }

    public function testDetectsHiddenFolder(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(
                overviewMode: OverviewMode::Transparent,
            ),
        );

        $this->assertTrue($folder->isTransparentInOverview());
    }

    public function testDetectsVisibleFolder(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(
                overviewMode: OverviewMode::Gallery,
            ),
        );

        $this->assertFalse($folder->isTransparentInOverview());
    }

    public function testDetectsGroupFolder(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(
                overviewMode: OverviewMode::Group,
            ),
        );

        $this->assertTrue($folder->isGroupInOverview());
    }

    public function testDetectsNoGroupFolder(): void
    {
        $folder = new GalleryFolder(
            slug: 'folder',
            title: 'Folder',
            filesystemDirectory: '/files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(
                overviewMode: OverviewMode::Gallery,
            ),
        );

        $this->assertFalse($folder->isGroupInOverview());
    }
}
