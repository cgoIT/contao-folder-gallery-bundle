<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Repository;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheKeyGenerator;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Repository\CachedGalleryRepository;
use Cgoit\ContaoFolderGalleryBundle\Repository\GalleryRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(CachedGalleryRepository::class)]
#[UsesClass(GalleryOverview::class)]
#[UsesClass(GalleryCacheKeyGenerator::class)]
final class CachedGalleryRepositoryTest extends TestCase
{
    public function testReturnsCachedOverview(): void
    {
        $overview = new GalleryOverview([], []);

        $cacheItem = $this->createStub(CacheItemInterface::class);
        $cacheItem
            ->method('isHit')
            ->willReturn(true)
        ;

        $cacheItem
            ->method('get')
            ->willReturn($overview)
        ;

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects($this->once())
            ->method('getItem')
            ->willReturn($cacheItem)
        ;

        $inner = $this->createMock(GalleryRepositoryInterface::class);
        $inner
            ->expects($this->never())
            ->method('findOverview')
        ;

        $cacheKeyGenerator = new GalleryCacheKeyGenerator();

        $repository = new CachedGalleryRepository($inner, $cache, $cacheKeyGenerator);

        $result = $repository->findOverview('/gallery');

        $this->assertSame($overview, $result);
    }

    public function testLoadsOverviewAndStoresItInCache(): void
    {
        $overview = new GalleryOverview([], []);

        $cacheItem = $this->createStub(CacheItemInterface::class);
        $cacheItem
            ->method('isHit')
            ->willReturn(false)
        ;

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects($this->once())
            ->method('getItem')
            ->willReturn($cacheItem)
        ;

        $inner = $this->createMock(GalleryRepositoryInterface::class);
        $inner
            ->expects($this->once())
            ->method('findOverview')
            ->willReturn($overview)
        ;

        $cache
            ->expects($this->once())
            ->method('save')
        ;

        $cacheKeyGenerator = new GalleryCacheKeyGenerator();

        $repository = new CachedGalleryRepository($inner, $cache, $cacheKeyGenerator);

        $result = $repository->findOverview('/gallery');

        $this->assertSame($overview, $result);
    }
}
