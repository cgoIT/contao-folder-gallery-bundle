<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Factory;

use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryBreadcrumbFactory;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GalleryBreadcrumbFactory::class)]
final class GalleryBreadcrumbFactoryTest extends ContaoTestCase
{
    public function testCreatesBreadcrumbsAndBackUrl(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('getFrontendUrl')
            ->willReturn('/gallery')
        ;

        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $gallery2026 = $this->createFolder('2026', ['2026'], OverviewMode::Gallery);

        $friday = $this->createFolder('Friday', ['2026', 'friday'], OverviewMode::Gallery);

        $bands = $this->createFolder('Bands', ['2026', 'friday', 'bands'], OverviewMode::Gallery);

        $overview = new GalleryOverview(
            new GalleryRoot('Root', 1, '/gallery'),
            [$gallery2026],
            [
                '2026' => $gallery2026,
                '2026/friday' => $friday,
                '2026/friday/bands' => $bands,
            ],
        );

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(4))
            ->method('generate')
            ->willReturnMap([
                [$page, null, '/gallery'],
                [$page, $gallery2026, '/gallery/2026'],
                [$page, $friday, '/gallery/2026/friday'],
                [$page, $bands, '/gallery/2026/friday/bands'],
            ])
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator, $this->createContaoFrameworkStub());

        $result = $factory->create($overview, $bands, $page);

        $this->assertSame('/gallery/2026/friday', $result['backUrl']);

        $this->assertCount(4, $result['breadcrumbs']);

        $this->assertSame('Gallery', $result['breadcrumbs'][0]->title);
        $this->assertSame('/gallery', $result['breadcrumbs'][0]->url);

        $this->assertSame('2026', $result['breadcrumbs'][1]->title);
        $this->assertSame('/gallery/2026', $result['breadcrumbs'][1]->url);

        $this->assertSame('Friday', $result['breadcrumbs'][2]->title);
        $this->assertSame('/gallery/2026/friday', $result['breadcrumbs'][2]->url);

        $this->assertSame('Bands', $result['breadcrumbs'][3]->title);
        $this->assertNull($result['breadcrumbs'][3]->url);
    }

    public function testHandlesGroupFolders(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('getFrontendUrl')
            ->willReturn('/gallery')
        ;

        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $gallery2026 = $this->createFolder('2026', ['2026'], OverviewMode::Gallery);

        $group = $this->createFolder('Music', ['2026', 'music'], OverviewMode::Group);

        $bands = $this->createFolder('Bands', ['2026', 'music', 'bands'], OverviewMode::Gallery);

        $overview = new GalleryOverview(
            new GalleryRoot('Root', 1, '/gallery'),
            [$gallery2026],
            [
                '2026' => $gallery2026,
                '2026/music' => $group,
                '2026/music/bands' => $bands,
            ],
        );

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(1))
            ->method('generateAnchor')
            ->willReturnMap([
                [$group, 'gallery-2026-music'],
            ])
        ;

        $urlGenerator
            ->expects($this->exactly(4))
            ->method('generate')
            ->willReturnMap([
                [$page, $gallery2026, '/gallery/2026'],
                [$page, null, '/gallery'],
                [$page, $bands, '/gallery/2026/music/bands'],
            ])
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator, $this->createContaoFrameworkStub());

        $result = $factory->create($overview, $bands, $page);

        $this->assertCount(4, $result['breadcrumbs']);

        $this->assertSame('Gallery', $result['breadcrumbs'][0]->title);
        $this->assertSame('/gallery', $result['breadcrumbs'][0]->url);

        $this->assertSame('2026', $result['breadcrumbs'][1]->title);
        $this->assertSame('/gallery/2026', $result['breadcrumbs'][1]->url);

        $this->assertSame('Music', $result['breadcrumbs'][2]->title);
        $this->assertSame(
            '/gallery/2026#gallery-2026-music',
            $result['breadcrumbs'][2]->url,
        );

        $this->assertSame('Bands', $result['breadcrumbs'][3]->title);
        $this->assertNull($result['breadcrumbs'][3]->url);
    }

    public function testHandlesGroupBeforeFirstGallery(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $group = $this->createFolder('2026', ['2026'], OverviewMode::Group);
        $friday = $this->createFolder('Friday', ['2026', 'friday'], OverviewMode::Gallery);

        $overview = new GalleryOverview(
            new GalleryRoot('Root', 1, '/gallery'),
            [$group],
            [
                '2026' => $group,
                '2026/friday' => $friday,
            ],
        );

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(3))
            ->method('generate')
            ->willReturnMap([
                [$page, null, '/gallery'],
                [$page, $friday, '/gallery/2026/friday'],
            ])
        ;

        $urlGenerator
            ->expects($this->once())
            ->method('generateAnchor')
            ->with($group)
            ->willReturn('gallery-2026')
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator, $this->createContaoFrameworkStub());

        $result = $factory->create($overview, $friday, $page);

        $this->assertCount(3, $result['breadcrumbs']);

        $this->assertSame('Gallery', $result['breadcrumbs'][0]->title);
        $this->assertSame('/gallery', $result['breadcrumbs'][0]->url);

        $this->assertSame('2026', $result['breadcrumbs'][1]->title);
        $this->assertSame('/gallery#gallery-2026', $result['breadcrumbs'][1]->url);

        $this->assertSame('Friday', $result['breadcrumbs'][2]->title);
        $this->assertNull($result['breadcrumbs'][2]->url);

        $this->assertSame('/gallery', $result['backUrl']);
    }

    public function testHandlesNestedGroups(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $gallery = $this->createFolder('2026', ['2026'], OverviewMode::Gallery);
        $music = $this->createFolder('Music', ['2026', 'music'], OverviewMode::Group);
        $rock = $this->createFolder('Rock', ['2026', 'music', 'rock'], OverviewMode::Group);
        $band = $this->createFolder('Band', ['2026', 'music', 'rock', 'band'], OverviewMode::Gallery);

        $overview = new GalleryOverview(
            new GalleryRoot('Root', 1, '/gallery'),
            [$gallery],
            [
                '2026' => $gallery,
                '2026/music' => $music,
                '2026/music/rock' => $rock,
                '2026/music/rock/band' => $band,
            ],
        );

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(5))
            ->method('generate')
            ->willReturnMap([
                [$page, null, '/gallery'],
                [$page, $gallery, '/gallery/2026'],
            ])
        ;

        $urlGenerator
            ->expects($this->exactly(2))
            ->method('generateAnchor')
            ->willReturnMap([
                [$music, 'gallery-2026-music'],
                [$rock, 'gallery-2026-music-rock'],
            ])
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator, $this->createContaoFrameworkStub());

        $result = $factory->create($overview, $band, $page);

        $this->assertCount(5, $result['breadcrumbs']);

        $this->assertSame('Gallery', $result['breadcrumbs'][0]->title);
        $this->assertSame('/gallery', $result['breadcrumbs'][0]->url);

        $this->assertSame('2026', $result['breadcrumbs'][1]->title);
        $this->assertSame('/gallery/2026', $result['breadcrumbs'][1]->url);

        $this->assertSame('Music', $result['breadcrumbs'][2]->title);
        $this->assertSame('/gallery/2026#gallery-2026-music', $result['breadcrumbs'][2]->url);

        $this->assertSame('Rock', $result['breadcrumbs'][3]->title);
        $this->assertSame('/gallery/2026#gallery-2026-music-rock', $result['breadcrumbs'][3]->url);

        $this->assertSame('Band', $result['breadcrumbs'][4]->title);
        $this->assertNull($result['breadcrumbs'][4]->url);

        $this->assertSame('/gallery/2026', $result['backUrl']);
    }

    public function testSkipsTransparentFolders(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['title', 'Gallery'],
            ])
        ;

        $gallery = $this->createFolder('2026', ['2026'], OverviewMode::Gallery);
        $music = $this->createFolder('Music', ['2026', 'music'], OverviewMode::Group);
        $hidden = $this->createFolder('Hidden', ['2026', 'music', 'hidden'], OverviewMode::Transparent);
        $band = $this->createFolder('Band', ['2026', 'music', 'hidden', 'band'], OverviewMode::Gallery);

        $overview = new GalleryOverview(
            new GalleryRoot('Root', 1, '/gallery'),
            [$gallery],
            [
                '2026' => $gallery,
                '2026/music' => $music,
                '2026/music/hidden' => $hidden,
                '2026/music/hidden/band' => $band,
            ],
        );

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(4))
            ->method('generate')
            ->willReturnMap([
                [$page, $gallery, '/gallery/2026'],
                [$page, $band, '/gallery/2026/music/hidden/band'],
                [$page, null, '/gallery'],
            ])
        ;

        $urlGenerator
            ->expects($this->once())
            ->method('generateAnchor')
            ->with($music)
            ->willReturn('gallery-2026-music')
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator, $this->createContaoFrameworkStub());

        $result = $factory->create($overview, $band, $page);

        $this->assertCount(4, $result['breadcrumbs']);

        $this->assertSame('Gallery', $result['breadcrumbs'][0]->title);
        $this->assertSame('/gallery', $result['breadcrumbs'][0]->url);

        $this->assertSame('2026', $result['breadcrumbs'][1]->title);
        $this->assertSame('/gallery/2026', $result['breadcrumbs'][1]->url);

        $this->assertSame('Music', $result['breadcrumbs'][2]->title);
        $this->assertSame('/gallery/2026#gallery-2026-music', $result['breadcrumbs'][2]->url);

        $this->assertSame('Band', $result['breadcrumbs'][3]->title);
        $this->assertNull($result['breadcrumbs'][3]->url);

        $this->assertSame('/gallery/2026', $result['backUrl']);
    }

    public function testResolvesPageAncestorsRootFirstExcludingCurrentPage(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['trail', [1, 2, 3]],
            ])
        ;

        $home = $this->createStub(PageModel::class);
        $home
            ->method('__get')
            ->willReturnMap([
                ['title', 'Start'],
                ['hide', false],
            ])
        ;

        $gallery = $this->createStub(PageModel::class);
        $gallery
            ->method('__get')
            ->willReturnMap([
                ['title', 'Galerie'],
                ['hide', false],
            ])
        ;

        $pageAdapter = $this->createAdapterMock(['findMultipleByIds']);
        $pageAdapter
            ->expects($this->once())
            ->method('findMultipleByIds')
            ->with([1, 2])
            ->willReturn([$home, $gallery])
        ;

        $framework = $this->createContaoFrameworkStub([PageModel::class => $pageAdapter]);

        $urlGenerator = $this->createMock(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnMap([
                [$home, null, '/'],
                [$gallery, null, '/galerie'],
            ])
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator, $framework);

        $result = $factory->createPageAncestors($page);

        $this->assertCount(2, $result);

        $this->assertSame('Start', $result[0]->title);
        $this->assertSame('/', $result[0]->url);

        $this->assertSame('Galerie', $result[1]->title);
        $this->assertSame('/galerie', $result[1]->url);
    }

    public function testSkipsHiddenPageAncestors(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['trail', [1, 2, 3]],
            ])
        ;

        $home = $this->createStub(PageModel::class);
        $home
            ->method('__get')
            ->willReturnMap([
                ['title', 'Start'],
                ['hide', true],
            ])
        ;

        $gallery = $this->createStub(PageModel::class);
        $gallery
            ->method('__get')
            ->willReturnMap([
                ['title', 'Galerie'],
                ['hide', false],
            ])
        ;

        $pageAdapter = $this->createAdapterStub(['findMultipleByIds']);
        $pageAdapter
            ->method('findMultipleByIds')
            ->willReturn([$home, $gallery])
        ;

        $framework = $this->createContaoFrameworkStub([PageModel::class => $pageAdapter]);

        $urlGenerator = $this->createStub(GalleryUrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturn('/galerie')
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator, $framework);

        $result = $factory->createPageAncestors($page);

        $this->assertCount(1, $result);
        $this->assertSame('Galerie', $result[0]->title);
    }

    public function testReturnsNoPageAncestorsForARootPage(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('__get')
            ->willReturnMap([
                ['trail', [1]],
            ])
        ;

        $framework = $this->createContaoFrameworkStub();

        $factory = new GalleryBreadcrumbFactory(
            $this->createStub(GalleryUrlGeneratorInterface::class),
            $framework,
        );

        $this->assertSame([], $factory->createPageAncestors($page));
    }

    /**
     * @param array<string> $trail
     */
    private function createFolder(string $title, array $trail, OverviewMode $mode): GalleryFolder
    {
        return new GalleryFolder(
            slug: end($trail),
            title: $title,
            filesystemDirectory: implode('/', $trail),
            trail: $trail,
            metadata: new GalleryMetadata(
                overviewMode: $mode,
            ),
        );
    }
}
