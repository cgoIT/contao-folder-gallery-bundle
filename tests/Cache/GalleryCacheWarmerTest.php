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
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryProviderInterface;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GalleryCacheWarmer::class)]
final class GalleryCacheWarmerTest extends ContaoTestCase
{
    public function testWarmsUpAllConfiguredGalleryRoots(): void
    {
        $contaoFramework = $this->createContaoFrameworkStub();

        $galleryProvider = $this->createMock(GalleryProviderInterface::class);
        $galleryProvider
            ->expects($this->once())
            ->method('findAllOverviews')
            ->willReturn([new GalleryOverview(
                new GalleryRoot('module', 1, '/files/gallery'),
                [],
                [],
            )])
        ;

        $warmer = new GalleryCacheWarmer($contaoFramework, $galleryProvider);

        $warmer->warmUp('/tmp');
    }
}
