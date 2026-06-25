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

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheWarmer;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Provider\CachedGalleryFolderProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryCacheWarmer::class)]
final class GalleryCacheWarmerTest extends TestCase
{
    public function testWarmsUpAllConfiguredGalleryRoots(): void
    {
        $rootProvider = $this->createMock(CachedGalleryFolderProviderInterface::class);
        $rootProvider
            ->expects($this->once())
            ->method('findAllOverviews')
            ->willReturn([new GalleryOverview('/files/gallery', [], [])])
        ;

        $warmer = new GalleryCacheWarmer($rootProvider);

        $warmer->warmUp('/tmp');
    }
}
