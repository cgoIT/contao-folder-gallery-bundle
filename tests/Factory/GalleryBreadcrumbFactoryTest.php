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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryBreadcrumbFactory::class)]
final class GalleryBreadcrumbFactoryTest extends TestCase
{
    public function testCreatesBreadcrumbsAndBackUrl(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('getFrontendUrl')
            ->willReturn('/gallery')
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
            ->expects($this->exactly(3))
            ->method('generate')
            ->willReturnMap([
                [$page, $gallery2026, '/gallery/2026'],
                [$page, $friday, '/gallery/2026/friday'],
                [$page, $bands, '/gallery/2026/friday/bands'],
            ])
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator);

        $result = $factory->create($overview, $bands, $page);

        $this->assertSame('/gallery/2026/friday', $result['backUrl']);

        $this->assertCount(3, $result['breadcrumbs']);

        $this->assertSame('2026', $result['breadcrumbs'][0]->title);
        $this->assertSame('/gallery/2026', $result['breadcrumbs'][0]->url);

        $this->assertSame('Friday', $result['breadcrumbs'][1]->title);
        $this->assertSame('/gallery/2026/friday', $result['breadcrumbs'][1]->url);

        $this->assertSame('Bands', $result['breadcrumbs'][2]->title);
        $this->assertNull($result['breadcrumbs'][2]->url);
    }

    public function testHandlesGroupFolders(): void
    {
        $page = $this->createStub(PageModel::class);
        $page
            ->method('getFrontendUrl')
            ->willReturn('/gallery')
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
            ->expects($this->exactly(3))
            ->method('generate')
            ->willReturnMap([
                [$page, $gallery2026, '/gallery/2026'],
                [$page, null, '/gallery'],
                [$page, $bands, '/gallery/2026/music/bands'],
            ])
        ;

        $factory = new GalleryBreadcrumbFactory($urlGenerator);

        $result = $factory->create($overview, $bands, $page);

        $this->assertCount(3, $result['breadcrumbs']);

        $this->assertSame('2026', $result['breadcrumbs'][0]->title);
        $this->assertSame('Music', $result['breadcrumbs'][1]->title);
        $this->assertSame('Bands', $result['breadcrumbs'][2]->title);

        $this->assertSame(
            '/gallery/2026#gallery-2026-music',
            $result['breadcrumbs'][1]->url,
        );

        $this->assertSame('/gallery/2026', $result['backUrl']);
    }

    public function testHandlesGroupBeforeFirstGallery(): void
    {
        $page = $this->createStub(PageModel::class);

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
            ->expects($this->exactly(2))
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

        $factory = new GalleryBreadcrumbFactory($urlGenerator);

        $result = $factory->create($overview, $friday, $page);

        $this->assertCount(2, $result['breadcrumbs']);

        $this->assertSame('2026', $result['breadcrumbs'][0]->title);
        $this->assertSame('/gallery#gallery-2026', $result['breadcrumbs'][0]->url);

        $this->assertSame('Friday', $result['breadcrumbs'][1]->title);
        $this->assertNull($result['breadcrumbs'][1]->url);

        $this->assertSame('/gallery', $result['backUrl']);
    }

    public function testHandlesNestedGroups(): void
    {
        $page = $this->createStub(PageModel::class);

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
            ->expects($this->exactly(4))
            ->method('generate')
            ->willReturnMap([
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

        $factory = new GalleryBreadcrumbFactory($urlGenerator);

        $result = $factory->create($overview, $band, $page);

        $this->assertCount(4, $result['breadcrumbs']);

        $this->assertSame('/gallery/2026#gallery-2026-music', $result['breadcrumbs'][1]->url);
        $this->assertSame('/gallery/2026#gallery-2026-music-rock', $result['breadcrumbs'][2]->url);

        $this->assertSame('/gallery/2026', $result['backUrl']);
    }

    public function testSkipsTransparentFolders(): void
    {
        $page = $this->createStub(PageModel::class);

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
            ->expects($this->exactly(3))
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

        $factory = new GalleryBreadcrumbFactory($urlGenerator);

        $result = $factory->create($overview, $band, $page);

        $this->assertCount(3, $result['breadcrumbs']);

        $this->assertSame('2026', $result['breadcrumbs'][0]->title);
        $this->assertSame('Music', $result['breadcrumbs'][1]->title);
        $this->assertSame('Band', $result['breadcrumbs'][2]->title);

        $this->assertSame('/gallery/2026#gallery-2026-music', $result['breadcrumbs'][1]->url);
        $this->assertNull($result['breadcrumbs'][2]->url);

        $this->assertSame('/gallery/2026', $result['backUrl']);
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
