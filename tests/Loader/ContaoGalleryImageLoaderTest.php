<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Loader;

use Cgoit\ContaoFolderGalleryBundle\Loader\ContaoGalleryImageLoader;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ContaoGalleryImageLoader::class)]
#[UsesClass(GalleryImage::class)]
final class ContaoGalleryImageLoaderTest extends ContaoTestCase
{
    public function testLoadsAndSortsImages(): void
    {
        $file1 = $this->createClassWithPropertiesStub(FilesModel::class, [
            'uuid' => StringUtil::uuidToBin('00000000-0000-0000-0000-000000000001'),
            'path' => '/gallery/image-b.jpg',
            'name' => 'image-b.jpg',
            'extension' => 'jpg',
        ]);

        $file2 = $this->createClassWithPropertiesStub(FilesModel::class, [
            'uuid' => StringUtil::uuidToBin('00000000-0000-0000-0000-000000000002'),
            'path' => '/gallery/image-a.jpg',
            'name' => 'image-a.jpg',
            'extension' => 'jpg',
        ]);

        $adapter = $this->createAdapterMock(['findMultipleFilesByFolder']);
        $adapter
            ->expects($this->once())
            ->method('findMultipleFilesByFolder')
            ->with('/gallery')
            ->willReturn([$file1, $file2])
        ;

        $framework = $this->createContaoFrameworkStub([FilesModel::class => $adapter]);

        $loader = new ContaoGalleryImageLoader($framework);

        $images = $loader->loadImages('/gallery', 'image-a.jpg');

        $this->assertCount(2, $images);

        $this->assertSame('image-a.jpg', $images[0]->filename);
        $this->assertTrue($images[0]->isCover);

        $this->assertSame('image-b.jpg', $images[1]->filename);
        $this->assertFalse($images[1]->isCover);
    }

    public function testIgnoresUnsupportedFiles(): void
    {
        $image = $this->createClassWithPropertiesStub(FilesModel::class, [
            'uuid' => StringUtil::uuidToBin('00000000-0000-0000-0000-000000000001'),
            'path' => '/gallery/image.jpg',
            'name' => 'image.jpg',
            'extension' => 'jpg',
        ]);

        $pdf = $this->createClassWithPropertiesStub(FilesModel::class, [
            'uuid' => StringUtil::uuidToBin('00000000-0000-0000-0000-000000000002'),
            'path' => '/gallery/test.pdf',
            'name' => 'test.pdf',
            'extension' => 'pdf',
        ]);
        $adapter = $this->createConfiguredAdapterStub(['findMultipleFilesByFolder' => [$image, $pdf]]);
        $framework = $this->createContaoFrameworkStub([FilesModel::class => $adapter]);

        $loader = new ContaoGalleryImageLoader($framework);

        $images = $loader->loadImages('/gallery', null);

        $this->assertCount(1, $images);
        $this->assertSame('image.jpg', $images[0]->filename);
    }

    public function testIgnoresMetadataAndDotFiles(): void
    {
        $dot = $this->createClassWithPropertiesStub(FilesModel::class, ['name' => '.', 'extension' => '']);
        $dotDot = $this->createClassWithPropertiesStub(FilesModel::class, ['name' => '..', 'extension' => '']);
        $metadata = $this->createClassWithPropertiesStub(FilesModel::class, ['name' => '_metadata.yml', 'extension' => 'yml']);
        $adapter = $this->createConfiguredAdapterStub(['findMultipleFilesByFolder' => [$dot, $dotDot, $metadata]]);
        $framework = $this->createContaoFrameworkStub([FilesModel::class => $adapter]);

        $loader = new ContaoGalleryImageLoader($framework);

        $images = $loader->loadImages('/gallery', null);

        $this->assertSame([], $images);
    }

    public function testReturnsEmptyArrayIfFolderContainsNoFiles(): void
    {
        $adapter = $this->createConfiguredAdapterStub(['findMultipleFilesByFolder' => []]);
        $framework = $this->createContaoFrameworkStub([FilesModel::class => $adapter]);

        $loader = new ContaoGalleryImageLoader($framework);

        $this->assertSame([], $loader->loadImages('/gallery', null));
    }

    public function testReturnsEmptyArrayIfAdapterReturnsNull(): void
    {
        $adapter = $this->createConfiguredAdapterStub(['findMultipleFilesByFolder' => null]);
        $framework = $this->createContaoFrameworkStub([FilesModel::class => $adapter]);

        $loader = new ContaoGalleryImageLoader($framework);

        $this->assertSame([], $loader->loadImages('/gallery', null));
    }
}
