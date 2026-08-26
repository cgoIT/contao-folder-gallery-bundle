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

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCache;
use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\EventListener\GalleryCacheInvalidateListener;
use Cgoit\ContaoFolderGalleryBundle\Matcher\GalleryPathMatcher;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Tests\TestCase;
use Contao\CoreBundle\Filesystem\Dbafs\ChangeSet\ChangeSet;
use Contao\CoreBundle\Filesystem\Dbafs\DbafsChangeEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[CoversClass(GalleryCacheInvalidateListener::class)]
#[UsesClass(GalleryCacheInvalidator::class)]
#[UsesClass(GalleryPathMatcher::class)]
final class GalleryCacheInvalidateListenerTest extends TestCase
{
    public function testInvalidatesCacheIfGalleryIsAffected(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(GalleryCache::getAllTags())
            ->willReturn(true)
        ;

        $invalidator = new GalleryCacheInvalidator($cache);

        $rootProvider = $this->createMock(GalleryRootProviderInterface::class);
        $rootProvider
            ->expects($this->once())
            ->method('getGalleryRoots')
            ->willReturn([
                new GalleryRoot('module-1', 1, 'files/gallery'),
                new GalleryRoot('module-2', 2, 'files/archive'),
            ])
        ;

        $framework = $this->createContaoFrameworkStub();

        $matcher = new GalleryPathMatcher($rootProvider);

        $listener = new GalleryCacheInvalidateListener($framework, $invalidator, $matcher);
        $listener(
            new DbafsChangeEvent(
                new ChangeSet([['hash' => '', 'path' => 'files/gallery', 'type' => ChangeSet::TYPE_DIRECTORY]], [], []),
            ),
        );
    }

    public function testDoesNotInvalidateCacheIfGalleryIsNotAffected(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache
            ->expects($this->never())
            ->method('invalidateTags')
            ->with(GalleryCache::getAllTags())
            ->willReturn(true)
        ;

        $invalidator = new GalleryCacheInvalidator($cache);

        $rootProvider = $this->createMock(GalleryRootProviderInterface::class);
        $rootProvider
            ->expects($this->once())
            ->method('getGalleryRoots')
            ->willReturn([
                new GalleryRoot('module-1', 1, 'files/gallery'),
                new GalleryRoot('module-2', 2, 'files/archive'),
            ])
        ;

        $framework = $this->createContaoFrameworkStub();

        $matcher = new GalleryPathMatcher($rootProvider);

        $listener = new GalleryCacheInvalidateListener($framework, $invalidator, $matcher);
        $listener(new DbafsChangeEvent(ChangeSet::createEmpty()));
    }
}
