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
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

final class GalleryCacheInvalidatorTest extends TestCase
{
    public function testInvalidateClearsCache(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true)
        ;

        $invalidator = new GalleryCacheInvalidator($cache);

        $invalidator->invalidate();
    }
}
