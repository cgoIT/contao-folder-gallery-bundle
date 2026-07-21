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

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCache;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFilesystemFingerprintProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[CoversClass(GalleryFilesystemFingerprintProvider::class)]
final class GalleryFilesystemFingerprintProviderTest extends TestCase
{
    private string $directory;

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/gallery_fingerprint_'.uniqid('', true);
        $this->filesystem = new Filesystem();

        $this->filesystem->mkdir($this->directory);

        $this->filesystem->touch($this->directory.'/image1.jpg');
        $this->filesystem->touch($this->directory.'/image2.webp');

        $this->filesystem->dumpFile(
            $this->directory.'/'.GalleryMetadata::METADATA_FILE_NAME,
            'title: Gallery',
        );

        $this->filesystem->touch($this->directory.'/notes.txt');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->directory);
    }

    public function testReturnsDeterministicFingerprint(): void
    {
        $provider = $this->createProvider();

        $fingerprint1 = $provider->getFilesystemFingerprint();
        $fingerprint2 = $provider->getFilesystemFingerprint();

        $this->assertSame($fingerprint1, $fingerprint2);
    }

    public function testFingerprintChangesWhenImageChanges(): void
    {
        $provider = $this->createProvider();

        $fingerprint1 = $provider->getFilesystemFingerprint();

        sleep(1);
        $this->filesystem->touch($this->directory.'/image1.jpg');
        clearstatcache();

        $provider = $this->createProvider();

        $fingerprint2 = $provider->getFilesystemFingerprint();

        $this->assertNotSame($fingerprint1, $fingerprint2);
    }

    public function testFingerprintChangesWhenMetadataChanges(): void
    {
        $provider = $this->createProvider();

        $fingerprint1 = $provider->getFilesystemFingerprint();

        sleep(1);

        $this->filesystem->dumpFile(
            $this->directory.'/'.GalleryMetadata::METADATA_FILE_NAME,
            'title: Changed',
        );

        clearstatcache();

        $provider = $this->createProvider();

        $fingerprint2 = $provider->getFilesystemFingerprint();

        $this->assertNotSame($fingerprint1, $fingerprint2);
    }

    public function testIgnoresIrrelevantFiles(): void
    {
        $provider = $this->createProvider();

        $fingerprint1 = $provider->getFilesystemFingerprint();

        sleep(1);

        $this->filesystem->dumpFile(
            $this->directory.'/notes.txt',
            'changed',
        );

        clearstatcache();

        $provider = $this->createProvider();

        $fingerprint2 = $provider->getFilesystemFingerprint();

        $this->assertSame($fingerprint1, $fingerprint2);
    }

    public function testUsesCache(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item
            ->expects($this->once())
            ->method('expiresAfter')
            ->with(10)
        ;

        $item
            ->expects($this->once())
            ->method('tag')
            ->with(GalleryCache::TAG_FILESYSTEM)
        ;

        $cache = $this->createMock(TagAwareCacheInterface::class);

        $cache
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(
                static function (string $key, callable $callback) use ($item): string {
                    static $value = null;

                    if (null === $value) {
                        $value = $callback($item);
                    }

                    return $value;
                },
            )
        ;

        $provider = new GalleryFilesystemFingerprintProvider(
            $this->createRootProvider(),
            $cache,
        );

        $fingerprint1 = $provider->getFilesystemFingerprint();
        $fingerprint2 = $provider->getFilesystemFingerprint();

        $this->assertSame($fingerprint1, $fingerprint2);
    }

    private function createProvider(): GalleryFilesystemFingerprintProvider
    {
        $item = $this->createStub(ItemInterface::class);

        $cache = $this->createStub(TagAwareCacheInterface::class);
        $cache
            ->method('get')
            ->willReturnCallback(
                static fn (string $key, callable $callback): string => $callback($item),
            )
        ;

        return new GalleryFilesystemFingerprintProvider(
            $this->createRootProvider(),
            $cache,
        );
    }

    private function createRootProvider(): GalleryRootProviderInterface
    {
        $provider = $this->createStub(GalleryRootProviderInterface::class);
        $provider
            ->method('getGalleryRoots')
            ->willReturn([
                new GalleryRoot(
                    moduleName: 'Gallery',
                    moduleId: 1,
                    filesystemDirectory: $this->directory,
                ),
            ])
        ;

        return $provider;
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = array_diff(scandir($directory), ['.', '..']);

        foreach ($files as $file) {
            $path = $directory.'/'.$file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                $this->filesystem->remove($path);
            }
        }

        $this->filesystem->remove($directory);
    }
}
