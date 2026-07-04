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

        $this->assertSame([], $provider->findAllOverviews());
    }

    public function testReturnsOverviewsFromSingleRoot(): void
    {
        $folder = $this->createFolder('year-2025');

        $overview = new GalleryOverview(filesystemDirectory: '/gallery', folders: [$folder], folderIndex: []);

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

        $overviews = $provider->findAllOverviews();

        $this->assertCount(1, $overviews);
        $this->assertSame(['year-2025'], $this->extractSlugs($overviews));
    }

    public function testReturnsOverviewsFromMultipleRoots(): void
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
                ['/gallery-a', new GalleryOverview('/gallery-a', [$folderA], [])],
                ['/gallery-b', new GalleryOverview('/gallery-b', [$folderB], [])],
            ])
        ;

        $provider = new GalleryFolderProvider($rootProvider, $repository);

        $overviews = $provider->findAllOverviews();

        $this->assertSame(
            ['year-2025', 'year-2026'],
            $this->extractSlugs($overviews),
        );
    }

    public function testReturnsFolderTreeForOverviewByRootPath(): void
    {
        $friday = $this->createFolder('friday');
        $saturday = $this->createFolder('saturday');

        $year2025 = $this->createFolder('year-2025', [$friday, $saturday]);

        $year2026 = $this->createFolder('year-2026');

        $overview = new GalleryOverview(filesystemDirectory: '/gallery', folders: [$year2025, $year2026], folderIndex: []);

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

        $overview = $provider->findOverviewByRootPath('/gallery');

        $this->assertCount(2, $overview->folders);
        $this->assertSame(
            [
                [
                    'slug' => 'year-2025',
                    'children' => [
                        ['slug' => 'friday', 'children' => []],
                        ['slug' => 'saturday', 'children' => []],
                    ],
                ],
                [
                    'slug' => 'year-2026',
                    'children' => [],
                ],
            ],
            $this->extractTree($overview->folders),
        );
    }

    public function testReturnsOverviewByRootPath(): void
    {
        $folder = $this->createFolder('year-2025');

        $overview = new GalleryOverview(
            filesystemDirectory: '/gallery',
            folders: [$folder],
            folderIndex: [],
        );

        $rootProvider = $this->createStub(GalleryRootProviderInterface::class);
        $rootProvider
            ->method('getGalleryRoots')
            ->willReturn(['/gallery'])
        ;

        $repository = $this->createMock(GalleryRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findOverview')
            ->with('/gallery', false)
            ->willReturn($overview)
        ;

        $provider = new GalleryFolderProvider($rootProvider, $repository);

        $result = $provider->findOverviewByRootPath('/gallery');

        $this->assertSame($overview, $result);
    }

    public function testReturnsNullIfOverviewRootPathDoesNotExist(): void
    {
        $rootProvider = $this->createStub(GalleryRootProviderInterface::class);
        $rootProvider
            ->method('getGalleryRoots')
            ->willReturn(['/gallery'])
        ;

        $repository = $this->createMock(GalleryRepositoryInterface::class);
        $repository
            ->expects($this->never())
            ->method('findOverview')
        ;

        $provider = new GalleryFolderProvider($rootProvider, $repository);

        $this->assertNotInstanceOf(
            GalleryOverview::class, $provider->findOverviewByRootPath('/unknown'),
        );
    }

    public function testReturnsFolderByPath(): void
    {
        $folder = $this->createFolder('year-2025');

        $overview = new GalleryOverview(
            filesystemDirectory: '/gallery',
            folders: [$folder],
            folderIndex: [
                'year-2025' => $folder,
            ],
        );

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

        $result = $provider->findFolderByPath('year-2025');

        $this->assertSame($folder, $result);
    }

    public function testReturnsNullIfFolderPathDoesNotExist(): void
    {
        $overview = new GalleryOverview(
            filesystemDirectory: '/gallery',
            folders: [],
            folderIndex: [],
        );

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

        $this->assertNotInstanceOf(GalleryFolder::class, $provider->findFolderByPath('unknown-folder'));
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
     * @param list<GalleryOverview> $overviews
     *
     * @return list<string>
     */
    private function extractSlugs(array $overviews): array
    {
        return array_merge(
            ...array_map(
                static fn (GalleryOverview $overview): array => array_map(
                    static fn (GalleryFolder $folder): string => $folder->slug,
                    $overview->folders,
                ),
                $overviews,
            ),
        );
    }

    /**
     * @param list<GalleryFolder> $folders
     *
     * @return array<mixed>
     */
    private function extractTree(array $folders): array
    {
        return array_map(
            fn (GalleryFolder $folder): array => [
                'slug' => $folder->slug,
                'children' => $this->extractTree($folder->folders),
            ],
            $folders,
        );
    }
}
