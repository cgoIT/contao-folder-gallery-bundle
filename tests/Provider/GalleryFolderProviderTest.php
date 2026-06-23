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

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFolderProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Repository\GalleryRepositoryInterface;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(GalleryFolderProvider::class)]
#[UsesClass(GalleryFolder::class)]
#[UsesClass(GalleryMetadata::class)]
final class GalleryFolderProviderTest extends ContaoTestCase
{
    public function testReturnsEmptyArrayIfNoRootsExist(): void
    {
        $rootProvider = $this->createStub(GalleryRootProviderInterface::class);
        $rootProvider
            ->method('getGalleryRoots')
            ->willReturn([])
        ;

        $repository = $this->createMock(GalleryRepositoryInterface::class);
        $repository
            ->expects($this->never())
            ->method('findOverview')
        ;

        $provider = new GalleryFolderProvider($rootProvider, $repository);

        $this->assertSame([], $provider->findAllFolders());
    }

    public function testReturnsFoldersFromSingleRoot(): void
    {
        $folder = $this->createFolder('year-2025');

        $overview = new GalleryOverview(folders: [$folder], folderIndex: []);

        $rootProvider = $this->createStub(GalleryRootProviderInterface::class);
        $rootProvider
            ->method('getGalleryRoots')
            ->willReturn(['/gallery'])
        ;

        $repository = $this->createMock(GalleryRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findOverview')
            ->with('/gallery')
            ->willReturn($overview)
        ;

        $provider = new GalleryFolderProvider($rootProvider, $repository);

        $folders = $provider->findAllFolders();

        $this->assertCount(1, $folders);
        $this->assertSame(['year-2025'], $this->extractSlugs($folders));
    }

    public function testReturnsFoldersFromMultipleRoots(): void
    {
        $folderA = $this->createFolder('year-2025');
        $folderB = $this->createFolder('year-2026');

        $rootProvider = $this->createStub(GalleryRootProviderInterface::class);
        $rootProvider
            ->method('getGalleryRoots')
            ->willReturn([
                '/gallery-a',
                '/gallery-b',
            ])
        ;

        $repository = $this->createMock(GalleryRepositoryInterface::class);
        $repository
            ->expects($this->exactly(2))
            ->method('findOverview')
            ->willReturnMap([
                ['/gallery-a', new GalleryOverview([$folderA], [])],
                ['/gallery-b', new GalleryOverview([$folderB], [])],
            ])
        ;

        $provider = new GalleryFolderProvider($rootProvider, $repository);

        $folders = $provider->findAllFolders();

        $this->assertSame(
            ['year-2025', 'year-2026'],
            $this->extractSlugs($folders),
        );
    }

    public function testReturnsFoldersRecursively(): void
    {
        $friday = $this->createFolder('friday');
        $saturday = $this->createFolder('saturday');

        $year2025 = $this->createFolder(
            'year-2025',
            [$friday, $saturday],
        );

        $year2026 = $this->createFolder('year-2026');

        $overview = new GalleryOverview(folders: [$year2025, $year2026], folderIndex: []);

        $rootProvider = $this->createStub(GalleryRootProviderInterface::class);
        $rootProvider
            ->method('getGalleryRoots')
            ->willReturn(['/gallery'])
        ;

        $repository = $this->createStub(GalleryRepositoryInterface::class);
        $repository
            ->method('findOverview')
            ->willReturn($overview)
        ;

        $provider = new GalleryFolderProvider($rootProvider, $repository);

        $folders = $provider->findAllFolders();

        $this->assertSame(
            [
                'year-2025',
                'friday',
                'saturday',
                'year-2026',
            ],
            $this->extractSlugs($folders),
        );
    }

    /**
     * @param list<GalleryFolder> $children
     */
    private function createFolder(string $slug, array $children = []): GalleryFolder
    {
        return new GalleryFolder(
            slug: $slug,
            title: $slug,
            filesystemDirectory: '/files/gallery/'.$slug,
            trail: [$slug],
            metadata: new GalleryMetadata(),
            folders: $children,
        );
    }

    /**
     * @param list<GalleryFolder> $folders
     *
     * @return list<string>
     */
    private function extractSlugs(array $folders): array
    {
        return array_map(
            static fn (GalleryFolder $folder): string => $folder->slug,
            $folders,
        );
    }
}
