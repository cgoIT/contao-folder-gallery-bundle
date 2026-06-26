<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Cache;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCache;
use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[CoversClass(GalleryCacheInvalidator::class)]
#[UsesClass(GalleryCache::class)]
final class GalleryCacheInvalidatorTest extends TestCase
{
    public function testInvalidateClearsCache(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(GalleryCache::getAllTags())
            ->willReturn(true)
        ;

        $invalidator = new GalleryCacheInvalidator($cache);

        $invalidator->invalidate();
    }
}
