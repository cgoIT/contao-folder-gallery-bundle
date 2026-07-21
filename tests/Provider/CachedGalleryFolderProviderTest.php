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
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Provider\CachedGalleryFolderProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFilesystemFingerprintProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFolderProviderInterface;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

#[CoversClass(CachedGalleryFolderProvider::class)]
#[UsesClass(GalleryFolder::class)]
#[UsesClass(GalleryMetadata::class)]
#[UsesClass(GalleryOverview::class)]
final class CachedGalleryFolderProviderTest extends ContaoTestCase
{
    private const string FINGERPRINT = 'fingerprint';

    public function testLoadsOverviewsFromInnerProviderOnCacheMiss(): void
    {
        $overview = new GalleryOverview(
            root: new GalleryRoot('module', 1, '/gallery'),
            folders: [],
            folderIndex: [],
        );

        $cache = new TagAwareAdapter(new ArrayAdapter());

        $inner = $this->createMock(GalleryFolderProviderInterface::class);
        $inner
            ->expects($this->once())
            ->method('findAllOverviews')
            ->with(false)
            ->willReturn([$overview])
        ;

        $filesystemFingerprintProvider = $this->createStub(GalleryFilesystemFingerprintProviderInterface::class);
        $filesystemFingerprintProvider
            ->method('getFilesystemFingerprint')
            ->willReturn(self::FINGERPRINT)
        ;

        $provider = new CachedGalleryFolderProvider(
            $inner,
            $cache,
            $filesystemFingerprintProvider,
        );

        $this->assertSame([$overview], $provider->findAllOverviews());
    }

    public function testLoadsOverviewsFromCacheOnHit(): void
    {
        $overview = new GalleryOverview(
            root: new GalleryRoot('module', 1, '/gallery'),
            folders: [],
            folderIndex: [],
        );

        $cache = new TagAwareAdapter(new ArrayAdapter());
        $item = $cache->getItem(
            \sprintf('%s.%s', GalleryCache::KEY_ALL_OVERVIEWS, self::FINGERPRINT),
        );

        $item->set([$overview]);
        $item->tag(GalleryCache::TAG_OVERVIEWS);

        $cache->save($item);

        $inner = $this->createMock(GalleryFolderProviderInterface::class);
        $inner
            ->expects($this->never())
            ->method('findAllOverviews')
        ;

        $filesystemFingerprintProvider = $this->createStub(GalleryFilesystemFingerprintProviderInterface::class);
        $filesystemFingerprintProvider
            ->method('getFilesystemFingerprint')
            ->willReturn(self::FINGERPRINT)
        ;

        $provider = new CachedGalleryFolderProvider(
            $inner,
            $cache,
            $filesystemFingerprintProvider,
        );

        $this->assertOverviewsEqual([$overview], $provider->findAllOverviews(true));
    }

    public function testFindsOverviewByRootPath(): void
    {
        $overview = new GalleryOverview(
            root: new GalleryRoot('module', 1, '/gallery'),
            folders: [],
            folderIndex: [],
        );

        $provider = $this->createProviderWithCachedOverviews([$overview]);

        $this->assertOverviewsEqual([$overview], [$provider->findOverviewByRootPath('/gallery')]);
    }

    public function testReturnsNullIfOverviewPathDoesNotExist(): void
    {
        $provider = $this->createProviderWithCachedOverviews([]);

        $this->assertNotInstanceOf(GalleryOverview::class, $provider->findOverviewByRootPath('/unknown'));
    }

    public function testFindsFolderByPath(): void
    {
        $folder = $this->createFolder('year-2025');

        $overview = new GalleryOverview(
            root: new GalleryRoot('module', 1, '/gallery'),
            folders: [$folder],
            folderIndex: [
                'year-2025' => $folder,
            ],
        );

        $provider = $this->createProviderWithCachedOverviews([$overview]);

        $this->assertFolderTreesEqual([$folder], [$provider->findFolderByPath('year-2025')]);
    }

    public function testReturnsNullIfFolderPathDoesNotExist(): void
    {
        $provider = $this->createProviderWithCachedOverviews([]);

        $this->assertNotInstanceOf(GalleryFolder::class, $provider->findFolderByPath('unknown'));
    }

    /**
     * @param list<GalleryOverview> $overviews
     */
    private function createProviderWithCachedOverviews(array $overviews): CachedGalleryFolderProvider
    {
        $cache = new TagAwareAdapter(new ArrayAdapter());
        $item = $cache->getItem(
            \sprintf(
                '%s.%s',
                GalleryCache::KEY_PUBLISHED_OVERVIEWS,
                self::FINGERPRINT,
            ),
        );

        $item->set($overviews);
        $item->tag(GalleryCache::TAG_OVERVIEWS);

        $cache->save($item);

        $filesystemFingerprintProvider = $this->createStub(GalleryFilesystemFingerprintProviderInterface::class);
        $filesystemFingerprintProvider
            ->method('getFilesystemFingerprint')
            ->willReturn(self::FINGERPRINT)
        ;

        return new CachedGalleryFolderProvider(
            $this->createStub(GalleryFolderProviderInterface::class),
            $cache,
            $filesystemFingerprintProvider,
        );
    }

    private function createFolder(string $slug): GalleryFolder
    {
        return new GalleryFolder(
            slug: $slug,
            title: $slug,
            filesystemDirectory: '/files/gallery/'.$slug,
            trail: [$slug],
            metadata: new GalleryMetadata(),
        );
    }

    /**
     * @param list<GalleryOverview> $expected
     * @param list<GalleryOverview> $actual
     */
    private function assertOverviewsEqual(array $expected, array $actual): void
    {
        $this->assertCount(\count($expected), $actual);

        foreach ($expected as $i => $overview) {
            $this->assertSame($overview->root->filesystemDirectory, $actual[$i]->root->filesystemDirectory);
            $this->assertFolderTreesEqual($overview->folders, $actual[$i]->folders);
            $this->assertSame(array_keys($overview->folderIndex), array_keys($actual[$i]->folderIndex));
        }
    }

    /**
     * @param list<GalleryFolder> $expected
     * @param list<GalleryFolder> $actual
     */
    private function assertFolderTreesEqual(array $expected, array $actual): void
    {
        $this->assertCount(\count($expected), $actual);

        foreach ($expected as $i => $folder) {
            $this->assertSame($folder->slug, $actual[$i]->slug);
            $this->assertSame($folder->title, $actual[$i]->title);
            $this->assertSame($folder->filesystemDirectory, $actual[$i]->filesystemDirectory);
            $this->assertSame($folder->trail, $actual[$i]->trail);

            $this->assertSame($folder->metadata->title, $actual[$i]->metadata->title);
            $this->assertSame($folder->metadata->overviewMode, $actual[$i]->metadata->overviewMode);

            $this->assertFolderTreesEqual($folder->folders, $actual[$i]->folders);
        }
    }
}
