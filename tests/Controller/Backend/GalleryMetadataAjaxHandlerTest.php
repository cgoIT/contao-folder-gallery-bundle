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
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('provideInvalidValues')]
    public function testRejectsValuesOutsideTheEditedFolder(string $value): void
    {
        $_POST['name'] = 'cover';
        $_POST['value'] = $value;

        $dc = $this->createStub(DataContainer::class);
        $dc
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_gallery_metadata'],
                ['id', 'files/gallery/a'],
            ])
        ;

        $GLOBALS['TL_DCA']['tl_gallery_metadata']['fields'] = ['cover' => ['inputType' => 'fileTree']];

        $handler = new GalleryMetadataAjaxHandler();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid path');

        try {
            $handler->executePostActions('reloadFiletree', $dc);
        } finally {
            unset($_POST['name'], $_POST['value']);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'other folder' => ['files/other/image.jpg'];
        yield 'sibling with same prefix' => ['files/gallery/ab/image.jpg'];
        yield 'traversal' => ['files/gallery/a/../../../config/secret.yml'];
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
}
