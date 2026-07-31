<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Provider;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryEntryPoint;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Model\SitemapEntry;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryEntryPointProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Provider\GallerySitemapProvider;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(GallerySitemapProvider::class)]
#[UsesClass(SitemapEntry::class)]
#[UsesClass(GalleryEntryPoint::class)]
#[UsesClass(GalleryRoot::class)]
final class GallerySitemapProviderTest extends ContaoTestCase
{
    public function testCreatesEntriesForGalleryFolders(): void
    {
        $page = $this->createStub(PageModel::class);
        $root = new GalleryRoot('Gallery', 1, 'files/gallery');

        $entryPoint = new GalleryEntryPoint($root, $page);

        $folder1 = $this->createFolder('folder1');
        $folder2 = $this->createFolder('folder2');

        $overview = new GalleryOverview(
            root: $root,
            folders: [$folder1, $folder2],
            folderIndex: [
                'one' => $folder1,
                'two' => $folder2,
            ],
        );

        $entryPointProvider = $this->createMock(GalleryEntryPointProviderInterface::class);
        $entryPointProvider
            ->expects($this->once())
            ->method('getEntryPoints')
            ->willReturn([$entryPoint])
        ;

        $galleryProvider = $this->createMock(GalleryProviderInterface::class);
        $galleryProvider
            ->expects($this->once())
            ->method('findOverviewByRootPath')
            ->with('files/gallery')
            ->willReturn($overview)
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $matcher = $this->exactly(2);
        $urlGenerator
            ->expects($matcher)
            ->method('generate')
            ->willReturnCallback(
                function (...$parameters) use ($matcher, $page, $folder1, $folder2): string {
                    if (1 === $matcher->numberOfInvocations()) {
                        $this->assertSame($page, $parameters[0]);
                        $this->assertSame($folder1, $parameters[1]);

                        return '/gallery/one';
                    }
                    if (2 === $matcher->numberOfInvocations()) {
                        $this->assertSame($page, $parameters[0]);
                        $this->assertSame($folder2, $parameters[1]);

                        return '/gallery/two';
                    }

                    return '';
                },
            )
        ;

        $provider = new GallerySitemapProvider(
            $entryPointProvider,
            $galleryProvider,
            $urlGenerator,
        );

        $entries = $provider->getEntries();

        $this->assertCount(2, $entries);
        $this->assertContainsOnlyInstancesOf(SitemapEntry::class, $entries);
    }

    public function testReturnsEmptyArrayIfOverviewCannotBeFound(): void
    {
        $page = $this->createStub(PageModel::class);
        $root = new GalleryRoot('Gallery', 1, 'files/gallery');

        $entryPoint = new GalleryEntryPoint($root, $page);

        $entryPointProvider = $this->createStub(GalleryEntryPointProviderInterface::class);
        $entryPointProvider
            ->method('getEntryPoints')
            ->willReturn([$entryPoint])
        ;

        $galleryProvider = $this->createMock(GalleryProviderInterface::class);
        $galleryProvider
            ->expects($this->once())
            ->method('findOverviewByRootPath')
            ->willReturn(null)
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->never())
            ->method('generate')
        ;

        $provider = new GallerySitemapProvider(
            $entryPointProvider,
            $galleryProvider,
            $urlGenerator,
        );

        $this->assertSame([], $provider->getEntries());
    }

    public function testMergesEntriesFromMultipleEntryPoints(): void
    {
        $page1 = $this->createStub(PageModel::class);
        $page2 = $this->createStub(PageModel::class);

        $entryPoint1 = new GalleryEntryPoint(
            new GalleryRoot('gallery-1', 1, '/gallery-1'),
            $page1,
        );

        $entryPoint2 = new GalleryEntryPoint(
            new GalleryRoot('gallery-2', 2, '/gallery-2'),
            $page2,
        );

        $folder1 = $this->createFolder('folder1');
        $folder2 = $this->createFolder('folder2');

        $overview1 = new GalleryOverview(
            root: $entryPoint1->galleryRoot,
            folders: [$folder1],
            folderIndex: ['one' => $folder1],
        );

        $overview2 = new GalleryOverview(
            root: $entryPoint2->galleryRoot,
            folders: [$folder2],
            folderIndex: ['two' => $folder2],
        );

        $entryPointProvider = $this->createStub(GalleryEntryPointProviderInterface::class);
        $entryPointProvider
            ->method('getEntryPoints')
            ->willReturn([$entryPoint1, $entryPoint2])
        ;

        $galleryProvider = $this->createMock(GalleryProviderInterface::class);
        $galleryProvider
            ->expects($this->exactly(2))
            ->method('findOverviewByRootPath')
            ->willReturnMap([
                ['/gallery-1', $overview1],
                ['/gallery-2', $overview2],
            ])
        ;

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                '/gallery-1',
                '/gallery-2',
            )
        ;

        $provider = new GallerySitemapProvider(
            $entryPointProvider,
            $galleryProvider,
            $urlGenerator,
        );

        $this->assertCount(2, $provider->getEntries());
    }

    private function createFolder(string $slug): GalleryFolder
    {
        return new GalleryFolder(
            slug: $slug,
            title: $slug,
            filesystemDirectory: '/files/gallery/'.$slug,
            trail: [$slug],
            metadata: new GalleryMetadata(),
        );
    }
}
