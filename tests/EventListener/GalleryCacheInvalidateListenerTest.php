<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\EventListener;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\EventListener\GalleryCacheInvalidateListener;
use Cgoit\ContaoFolderGalleryBundle\Matcher\GalleryPathMatcher;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Contao\CoreBundle\Filesystem\Dbafs\ChangeSet\ChangeSet;
use Contao\CoreBundle\Filesystem\Dbafs\DbafsChangeEvent;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

final class GalleryCacheInvalidateListenerTest extends TestCase
{
    public function testInvalidatesCacheIfGalleryIsAffected(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true)
        ;

        $invalidator = new GalleryCacheInvalidator($cache);

        $rootProvider = $this->createMock(GalleryRootProviderInterface::class);
        $rootProvider
            ->expects($this->once())
            ->method('getGalleryRoots')
            ->willReturn([
                'files/gallery',
                'files/archive',
            ])
        ;

        $matcher = new GalleryPathMatcher($rootProvider);

        $listener = new GalleryCacheInvalidateListener($invalidator, $matcher);

        $listener(
            new DbafsChangeEvent(
                new ChangeSet([['hash' => '', 'path' => 'files/gallery', 'type' => ChangeSet::TYPE_DIRECTORY]], [], []),
            ),
        );
    }

    public function testDoesNotInvalidateCacheIfGalleryIsNotAffected(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects($this->never())
            ->method('clear')
        ;

        $invalidator = new GalleryCacheInvalidator($cache);

        $rootProvider = $this->createMock(GalleryRootProviderInterface::class);
        $rootProvider
            ->expects($this->once())
            ->method('getGalleryRoots')
            ->willReturn([
                'files/gallery',
                'files/archive',
            ])
        ;

        $matcher = new GalleryPathMatcher($rootProvider);

        $listener = new GalleryCacheInvalidateListener(
            $invalidator,
            $matcher,
        );

        $listener(
            new DbafsChangeEvent(
                ChangeSet::createEmpty(),
            ),
        );
    }
}
