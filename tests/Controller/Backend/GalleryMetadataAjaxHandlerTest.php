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

use Cgoit\ContaoFolderGalleryBundle\Controller\Backend\GalleryMetadataAjaxHandler;
use Contao\DataContainer;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[CoversClass(GalleryMetadataAjaxHandler::class)]
final class GalleryMetadataAjaxHandlerTest extends ContaoTestCase
{
    public function testIgnoresUnknownAction(): void
    {
        $dc = $this->createMock(DataContainer::class);
        $dc
            ->expects($this->never())
            ->method('__get')
            ->with('table')
            ->willReturn('tl_gallery_metadata')
        ;

        $handler = new GalleryMetadataAjaxHandler();

        $handler->executePostActions('foo', $dc);

        $this->addToAssertionCount(1);
    }

    public function testThrowsExceptionIfFieldDoesNotExist(): void
    {
        $_GET['id'] = 'files/gallery';
        $_POST['name'] = 'foo';

        $dc = $this->createMock(DataContainer::class);
        $dc
            ->expects($this->once())
            ->method('__get')
            ->with('table')
            ->willReturn('tl_gallery_metadata')
        ;

        $GLOBALS['TL_DCA']['tl_gallery_metadata']['fields'] = [];

        $handler = new GalleryMetadataAjaxHandler();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid field name: foo');

        $handler->executePostActions('reloadFiletree', $dc);
    }

    public function testThrowsExceptionIfImageIsOutsideGalleryFolder(): void
    {
        $_GET['id'] = 'files/gallery';
        $_POST['name'] = 'cover';
        $_POST['value'] = 'files/other/image.jpg';

        $dc = $this->createMock(DataContainer::class);
        $dc
            ->expects($this->exactly(2))
            ->method('__get')
            ->with('table')
            ->willReturn('tl_gallery_metadata')
        ;

        $GLOBALS['TL_DCA']['tl_gallery_metadata']['fields']['cover'] = [];

        $handler = new GalleryMetadataAjaxHandler();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Image from invalid folder selected');

        $handler->executePostActions('reloadFiletree', $dc);
    }
}
