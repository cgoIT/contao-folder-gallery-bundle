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

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheKeyGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(GalleryCacheInvalidator::class)]
final class GalleryCacheInvalidatorTest extends TestCase
{
    public function testInvalidateClearsCache(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects($this->exactly(2))
            ->method('deleteItem')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $generator = new GalleryCacheKeyGenerator();

        $invalidator = new GalleryCacheInvalidator($cache, $generator);

        $invalidator->invalidate();
    }
}
