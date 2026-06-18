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
use Cgoit\ContaoFolderGalleryBundle\Loader\MetadataLoader;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
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
            new MetadataLoader(),
            $imageLoader,
            $slug,
        );
    }

    public function testFindOverview(): void
    {
        $overview = $this->repository->findOverview(
            self::FIXTURE_PATH,
        );

        $this->assertCount(2, $overview->folders);

        $year2025 = $overview->folders[1];

        $this->assertSame('Year 2025', $year2025->title);
        $this->assertSame('year-2025', $year2025->slug);
        $this->assertCount(2, $year2025->folders);
        $this->assertSame('year-2025', $year2025->getPath());
        $this->assertFriday2025($year2025->folders[1]);

        $year2026 = $overview->folders[0];
        $this->assertSame('Year 2026', $year2026->title);
        $this->assertSame('year-2026', $year2026->slug);
        $this->assertCount(0, $year2026->folders);
        $this->assertSame('year-2026', $year2026->getPath());
    }

    public function testFindFolderByPath(): void
    {
        $folder = $this->repository->findFolderByPath(
            __DIR__.'/../Fixtures/gallery',
            'year-2025/friday-year-2025',
        );

        $this->assertInstanceOf(GalleryFolder::class, $folder);
        $this->assertSame('Friday Year 2025', $folder->title);
    }

    public function testFindFolderByPathReturnsNullForUnknownPath(): void
    {
        $folder = $this->repository->findFolderByPath(
            __DIR__.'/../Fixtures/gallery',
            'does/not/exist',
        );

        $this->assertNotInstanceOf(GalleryFolder::class, $folder);
    }

    private function assertFriday2025(GalleryFolder $folder): void
    {
        $this->assertSame('Friday Year 2025', $folder->title);
        $this->assertSame('friday-year-2025', $folder->slug);
        $this->assertCount(3, $folder->images);
        $this->assertSame('image2.jpg', $folder->images[1]->filename);
        $this->assertTrue($folder->images[1]->isCover);
        $this->assertSame('year-2025/friday-year-2025', $folder->getPath());
    }
}
