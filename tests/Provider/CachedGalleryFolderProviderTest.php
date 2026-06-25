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

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheKeyGenerator;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Provider\CachedGalleryFolderProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFolderProviderInterface;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(CachedGalleryFolderProvider::class)]
#[UsesClass(GalleryFolder::class)]
#[UsesClass(GalleryMetadata::class)]
#[UsesClass(GalleryOverview::class)]
final class CachedGalleryFolderProviderTest extends ContaoTestCase
{
    public function testLoadsOverviewsFromInnerProviderOnCacheMiss(): void
    {
        $overview = new GalleryOverview(
            filesystemDirectory: '/gallery',
            folders: [],
            folderIndex: [],
        );

        $item = $this->createMock(CacheItemInterface::class);
        $item
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false)
        ;

        $item
            ->expects($this->once())
            ->method('set')
            ->with([$overview])
            ->willReturnSelf()
        ;

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects($this->once())
            ->method('getItem')
            ->with('all_folder_gallery_overviews_0')
            ->willReturn($item)
        ;

        $cache
            ->expects($this->once())
            ->method('save')
            ->with($item)
        ;

        $inner = $this->createMock(GalleryFolderProviderInterface::class);
        $inner
            ->expects($this->once())
            ->method('findAllOverviews')
            ->with(false)
            ->willReturn([$overview])
        ;

        $generator = new GalleryCacheKeyGenerator();

        $provider = new CachedGalleryFolderProvider(
            $inner,
            $cache,
            $generator,
        );

        $this->assertSame([$overview], $provider->findAllOverviews());
    }

    public function testLoadsOverviewsFromCacheOnHit(): void
    {
        $overview = new GalleryOverview(
            filesystemDirectory: '/gallery',
            folders: [],
            folderIndex: [],
        );

        $item = $this->createMock(CacheItemInterface::class);
        $item
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(true)
        ;

        $item
            ->expects($this->once())
            ->method('get')
            ->willReturn([$overview])
        ;

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects($this->once())
            ->method('getItem')
            ->with('all_folder_gallery_overviews_0')
            ->willReturn($item)
        ;

        $inner = $this->createMock(GalleryFolderProviderInterface::class);
        $inner
            ->expects($this->never())
            ->method('findAllOverviews')
        ;

        $generator = new GalleryCacheKeyGenerator();

        $provider = new CachedGalleryFolderProvider(
            $inner,
            $cache,
            $generator,
        );

        $this->assertSame([$overview], $provider->findAllOverviews());
    }

    public function testFindsOverviewByRootPath(): void
    {
        $overview = new GalleryOverview(
            filesystemDirectory: '/gallery',
            folders: [],
            folderIndex: [],
        );

        $provider = $this->createProviderWithCachedOverviews([$overview]);

        $this->assertSame($overview, $provider->findOverviewByRootPath('/gallery'));
    }

    public function testReturnsNullIfOverviewPathDoesNotExist(): void
    {
        $provider = $this->createProviderWithCachedOverviews([]);

        $this->assertNotInstanceOf(GalleryOverview::class, $provider->findOverviewByRootPath('/unknown'));
    }

    public function testFindsFolderByPath(): void
    {
        $folder = $this->createFolder('year-2025');

        $overview = new GalleryOverview(
            filesystemDirectory: '/gallery',
            folders: [$folder],
            folderIndex: [
                'year-2025' => $folder,
            ],
        );

        $provider = $this->createProviderWithCachedOverviews([$overview]);

        $this->assertSame($folder, $provider->findFolderByPath('year-2025'));
    }

    public function testReturnsNullIfFolderPathDoesNotExist(): void
    {
        $provider = $this->createProviderWithCachedOverviews([]);

        $this->assertNotInstanceOf(GalleryFolder::class, $provider->findFolderByPath('unknown'));
    }

    /**
     * @param list<GalleryOverview> $overviews
     */
    private function createProviderWithCachedOverviews(array $overviews): CachedGalleryFolderProvider
    {
        $item = $this->createStub(CacheItemInterface::class);
        $item
            ->method('isHit')
            ->willReturn(true)
        ;

        $item
            ->method('get')
            ->willReturn($overviews)
        ;

        $cache = $this->createStub(CacheItemPoolInterface::class);
        $cache
            ->method('getItem')
            ->willReturn($item)
        ;

        $generator = new GalleryCacheKeyGenerator();

        return new CachedGalleryFolderProvider(
            $this->createStub(GalleryFolderProviderInterface::class),
            $cache,
            $generator,
        );
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
