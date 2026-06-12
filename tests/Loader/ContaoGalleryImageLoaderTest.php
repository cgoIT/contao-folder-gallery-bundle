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
use Cgoit\ContaoFolderGalleryBundle\Provider\FilesModelProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Provider\ImageFile;
use Contao\StringUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class ContaoGalleryImageLoaderTest extends TestCase
{
    private ContaoGalleryImageLoader $loader;

    protected function setUp(): void
    {
        $provider = $this->createStub(FilesModelProviderInterface::class);
        $provider
            ->method('findByPath')
            ->willReturnCallback(
                static fn (string $path): ImageFile => new ImageFile(
                    '12345678-1234-1234-1234-123456789abc',
                    $path,
                ),
            )
        ;

        $this->loader = new ContaoGalleryImageLoader($provider);
    }

    public function testLoadsImages(): void
    {
        $directory = $this->createFixtureDirectory([
            'image1.jpg',
            'image2.png',
            'cover.jpg',
            '_metadata.yml',
            'notes.txt',
        ]);

        $images = $this->loader->loadImages($directory, 'cover.jpg');

        $this->assertCount(3, $images);
        $this->assertSame('cover.jpg', $images[0]->filename);
        $this->assertSame('image1.jpg', $images[1]->filename);
        $this->assertSame('image2.png', $images[2]->filename);
    }

    public function testMarksCoverImage(): void
    {
        $directory = $this->createFixtureDirectory([
            'cover.jpg',
            'image1.jpg',
        ]);

        $images = $this->loader->loadImages($directory, 'cover.jpg');

        $this->assertTrue($images[0]->isCover);
        $this->assertFalse($images[1]->isCover);
    }

    public function testCreatesUuidFromFilesModel(): void
    {
        $directory = $this->createFixtureDirectory([
            'image1.jpg',
        ]);

        $images = $this->loader->loadImages($directory, null);

        $this->assertCount(1, $images);
        $this->assertSame(StringUtil::binToUuid('12345678-1234-1234-1234-123456789abc'), $images[0]->uuid);
    }

    /**
     * @param array<mixed> $files
     */
    private function createFixtureDirectory(array $files): string
    {
        $directory = sys_get_temp_dir().'/gallery-loader-'.uniqid();

        $fs = new Filesystem();

        $fs->mkdir($directory);

        foreach ($files as $file) {
            $fs->touch($directory.'/'.$file);
        }

        return $directory;
    }
}
