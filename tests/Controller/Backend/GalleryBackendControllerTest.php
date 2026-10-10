<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Controller\Backend;

use Cgoit\ContaoFolderGalleryBundle\Controller\Backend\GalleryBackendController;
use Cgoit\ContaoFolderGalleryBundle\Controller\Backend\GalleryMetadataAjaxHandler;
use Cgoit\ContaoFolderGalleryBundle\Drivers\DC_GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Security\GalleryBackendAccess;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DataContainer\ButtonsBuilder;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(GalleryBackendController::class)]
#[UsesClass(GalleryBackendAccess::class)]
final class GalleryBackendControllerTest extends ContaoTestCase
{
    public function testDeniesAccessWithoutModulePermission(): void
    {
        $provider = $this->createMock(GalleryProviderInterface::class);
        $provider
            ->expects($this->never())
            ->method('findAllOverviews')
        ;

        $security = $this->createStub(Security::class);
        $security
            ->method('isGranted')
            ->willReturn(false)
        ;

        $controller = new GalleryBackendController(
            $this->createContaoFrameworkStub(),
            $this->createStub(ButtonsBuilder::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(ContaoCsrfTokenManager::class),
            $provider,
            new GalleryMetadataAjaxHandler(),
            $this->createStub(DC_GalleryMetadata::class),
            new GalleryBackendAccess($security),
        );

        $this->expectException(AccessDeniedException::class);

        $controller(new Request());
    }
}
