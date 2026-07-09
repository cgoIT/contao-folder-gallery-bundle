<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\EventListener\DataContainer;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\EventListener\DataContainer\ModuleCallbacks;
use Contao\BackendUser;
use Contao\CoreBundle\Image\ImageSizes;
use Contao\CoreBundle\Twig\Finder\FinderFactory;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\SecurityBundle\Security;

#[CoversClass(ModuleCallbacks::class)]
final class ModuleCallbacksTest extends ContaoTestCase
{
    public function testReturnsEmptyArrayIfUserIsNotBackendUser(): void
    {
        $security = $this->createMock(Security::class);
        $security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $imageSizes = $this->createMock(ImageSizes::class);
        $imageSizes
            ->expects($this->never())
            ->method('getOptionsForUser')
        ;

        $galleryCacheInvalidator = $this->createMock(GalleryCacheInvalidator::class);
        $galleryCacheInvalidator
            ->expects($this->never())
            ->method('invalidate')
        ;

        $callbacks = new ModuleCallbacks(
            $this->createStub(FinderFactory::class),
            $security,
            $imageSizes,
            $galleryCacheInvalidator,
        );

        $this->assertSame([], $callbacks->getImageSizes());
    }

    public function testReturnsImageSizesForBackendUser(): void
    {
        $user = $this->createStub(BackendUser::class);

        $security = $this->createMock(Security::class);
        $security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $imageSizes = $this->createMock(ImageSizes::class);
        $imageSizes
            ->expects($this->once())
            ->method('getOptionsForUser')
            ->with($user)
            ->willReturn([
                'small',
                'large',
            ])
        ;

        $galleryCacheInvalidator = $this->createMock(GalleryCacheInvalidator::class);
        $galleryCacheInvalidator
            ->expects($this->never())
            ->method('invalidate')
        ;

        $callbacks = new ModuleCallbacks(
            $this->createStub(FinderFactory::class),
            $security,
            $imageSizes,
            $galleryCacheInvalidator,
        );

        $this->assertSame(['small', 'large'], $callbacks->getImageSizes());
    }
}
