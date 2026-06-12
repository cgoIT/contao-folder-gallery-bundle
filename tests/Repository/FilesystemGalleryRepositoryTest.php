<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Repository;

use Cgoit\ContaoFolderGalleryBundle\Loader\GalleryImageLoaderInterface;
use Cgoit\ContaoFolderGalleryBundle\Metadata\MetadataReader;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryDay;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Repository\FilesystemGalleryRepository;
use Contao\CoreBundle\Slug\Slug;
use Contao\StringUtil;
use PHPUnit\Framework\TestCase;

final class FilesystemGalleryRepositoryTest extends TestCase
{
    private const string FIXTURE_PATH = __DIR__.'/../Fixtures/gallery';

    private FilesystemGalleryRepository $repository;

    protected function setUp(): void
    {
        $imageLoader = $this->createStub(GalleryImageLoaderInterface::class);
        $imageLoader
            ->method('loadImages')
            ->willReturn([
                new GalleryImage(
                    uuid: StringUtil::binToUuid('00000000-0000-0000-0000-000000000000'),
                    path: self::FIXTURE_PATH,
                    filename: 'image1.jpg',
                    isCover: false,
                ),
                new GalleryImage(
                    uuid: StringUtil::binToUuid('00000000-0000-0000-0000-000000000001'),
                    path: self::FIXTURE_PATH,
                    filename: 'image2.jpg',
                    isCover: true,
                ),
                new GalleryImage(
                    uuid: StringUtil::binToUuid('00000000-0000-0000-0000-000000000002'),
                    path: self::FIXTURE_PATH,
                    filename: 'image3.jpg',
                    isCover: false,
                ),
            ])
        ;

        $slug = $this->createStub(Slug::class);
        $slug
            ->method('generate')
            ->willReturnCallback(
                static fn (string $input): string => StringUtil::generateAlias($input),
            )
        ;

        $this->repository = new FilesystemGalleryRepository(
            new MetadataReader(),
            $imageLoader,
            $slug,
        );
    }

    public function testFindOverview(): void
    {
        $overview = $this->repository->findOverview(
            self::FIXTURE_PATH,
        );

        $this->assertCount(2, $overview->years);

        $year2025 = $overview->years[1];

        $this->assertSame('Year 2025', $year2025->title);
        $this->assertSame('year-2025', $year2025->slug);
        $this->assertCount(2, $year2025->days);
        $this->assertFriday2025($year2025->days[0]);

        $year2026 = $overview->years[0];
        $this->assertSame('Year 2026', $year2026->title);
        $this->assertSame('year-2026', $year2026->slug);
        $this->assertCount(0, $year2026->days);
    }

    public function testFindDay(): void
    {
        $day = $this->repository->findDay(
            self::FIXTURE_PATH,
            '2025',
            '01-Friday',
        );

        $this->assertInstanceOf(GalleryDay::class, $day);
        $this->assertFriday2025($day);
    }

    public function testReturnsNullForUnknownDay(): void
    {
        $day = $this->repository->findDay(
            self::FIXTURE_PATH,
            'unknown-year',
            'unknown-day',
        );

        $this->assertNotInstanceOf(GalleryDay::class, $day);
    }

    private function assertFriday2025(GalleryDay $day): void
    {
        $this->assertSame('Friday Year 2025', $day->title);
        $this->assertSame('friday-year-2025', $day->slug);
        $this->assertCount(3, $day->images);
        $this->assertSame('image2.jpg', $day->images[1]->filename);
        $this->assertTrue($day->images[1]->isCover);
    }
}
