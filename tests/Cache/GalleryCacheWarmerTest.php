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
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Repository\GalleryRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryCacheWarmer::class)]
final class GalleryCacheWarmerTest extends TestCase
{
    public function testWarmsUpAllConfiguredGalleryRoots(): void
    {
        $rootProvider = $this->createMock(GalleryRootProviderInterface::class);
        $rootProvider
            ->expects($this->once())
            ->method('getGalleryRoots')
            ->willReturn([
                'files/gallery',
                'files/archive',
            ])
        ;

        $repository = $this->createMock(GalleryRepositoryInterface::class);
        $repository
            ->expects($this->exactly(2))
            ->method('findOverview')
            ->willReturn(new GalleryOverview([], []))
        ;

        $warmer = new GalleryCacheWarmer($rootProvider, $repository);

        $warmer->warmUp('/tmp');
    }

    public function testDoesNothingIfNoGalleryRootsExist(): void
    {
        $rootProvider = $this->createMock(GalleryRootProviderInterface::class);
        $rootProvider
            ->expects($this->once())
            ->method('getGalleryRoots')
            ->willReturn([])
        ;

        $repository = $this->createMock(GalleryRepositoryInterface::class);
        $repository
            ->expects($this->never())
            ->method('findOverview')
        ;

        $warmer = new GalleryCacheWarmer($rootProvider, $repository);

        $warmer->warmUp('/tmp');
    }
}
