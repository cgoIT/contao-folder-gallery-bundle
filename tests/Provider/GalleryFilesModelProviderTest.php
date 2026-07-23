<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Provider;

use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFilesModelProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\ImageFile;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(GalleryFilesModelProvider::class)]
#[UsesClass(ImageFile::class)]
final class GalleryFilesModelProviderTest extends ContaoTestCase
{
    public function testReturnsImageFile(): void
    {
        $model = $this->createClassWithPropertiesStub(FilesModel::class, [
            'uuid' => StringUtil::uuidToBin('00000000-0000-0000-0000-000000000001'),
            'path' => 'files/gallery/image.jpg',
        ]);

        $adapter = $this->createAdapterMock(['findByPath']);
        $adapter
            ->expects($this->once())
            ->method('findByPath')
            ->with('files/gallery/image.jpg')
            ->willReturn($model)
        ;

        $framework = $this->createContaoFrameworkStub([FilesModel::class => $adapter]);

        $provider = new GalleryFilesModelProvider($framework);

        $result = $provider->findByPath('files/gallery/image.jpg');

        $this->assertInstanceOf(ImageFile::class, $result);
        $this->assertSame('00000000-0000-0000-0000-000000000001', $result->uuid);
        $this->assertSame('files/gallery/image.jpg', $result->path);
    }

    public function testReturnsNullIfModelWasNotFound(): void
    {
        $adapter = $this->createAdapterMock(['findByPath']);
        $adapter
            ->expects($this->once())
            ->method('findByPath')
            ->with('files/gallery/image.jpg')
            ->willReturn(null)
        ;

        $framework = $this->createContaoFrameworkStub([FilesModel::class => $adapter]);

        $provider = new GalleryFilesModelProvider($framework);

        $this->assertNotInstanceOf(ImageFile::class, $provider->findByPath('files/gallery/image.jpg'));
    }
}
