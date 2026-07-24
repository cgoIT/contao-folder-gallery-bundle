<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Model;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryMetadata::class)]
final class GalleryMetadataTest extends TestCase
{
    public function testIsPublishedReturnsTrueWhenNoDatesAreSet(): void
    {
        $metadata = new GalleryMetadata();

        $this->assertTrue($metadata->isPublished(), 'isPublished() should return true when no dates are set');
    }

    public function testIsPublishedReturnsTrueWhenNowIsBetweenDates(): void
    {
        $publishedFrom = new \DateTimeImmutable('2026-06-01');
        $publishedUntil = new \DateTimeImmutable('2026-06-30');
        $now = new \DateTimeImmutable('2026-06-15');

        $metadata = new GalleryMetadata(publishedFrom: $publishedFrom, publishedUntil: $publishedUntil);

        $this->assertTrue($metadata->isPublished($now), 'isPublished() should return true when now is between publishedFrom and publishedUntil');
    }

    public function testIsPublishedReturnsFalseWhenNowIsBeforePublishedFrom(): void
    {
        $publishedFrom = new \DateTimeImmutable('2026-06-15');
        $now = new \DateTimeImmutable('2026-06-10');

        $metadata = new GalleryMetadata(publishedFrom: $publishedFrom);

        $this->assertFalse($metadata->isPublished($now), 'isPublished() should return false when now is before publishedFrom');
    }

    public function testIsPublishedReturnsFalseWhenNowIsAfterPublishedUntil(): void
    {
        $publishedUntil = new \DateTimeImmutable('2026-06-10');
        $now = new \DateTimeImmutable('2026-06-15');

        $metadata = new GalleryMetadata(publishedUntil: $publishedUntil);

        $this->assertFalse($metadata->isPublished($now), 'isPublished() should return false when now is after publishedUntil');
    }

    public function testIsPublishedReturnsTrueWhenNowEqualsPublishedFrom(): void
    {
        $publishedFrom = new \DateTimeImmutable('2026-06-15');
        $now = new \DateTimeImmutable('2026-06-15');

        $metadata = new GalleryMetadata(publishedFrom: $publishedFrom);

        $this->assertTrue($metadata->isPublished($now), 'isPublished() should return true when now equals publishedFrom');
    }

    public function testIsPublishedReturnsTrueWhenNowEqualsPublishedUntil(): void
    {
        $publishedUntil = new \DateTimeImmutable('2026-06-15');
        $now = new \DateTimeImmutable('2026-06-15');

        $metadata = new GalleryMetadata(publishedUntil: $publishedUntil);

        $this->assertTrue($metadata->isPublished($now), 'isPublished() should return true when now equals publishedUntil');
    }

    public function testReturnsCurrentRecord(): void
    {
        $publishedFrom = new \DateTimeImmutable('2025-01-01');
        $publishedUntil = new \DateTimeImmutable('2025-12-31');

        $metadata = new GalleryMetadata(
            title: 'Gallery 2025',
            description: 'Description',
            cover: 'cover.jpg',
            hideCoverInGallery: false,
            publishedFrom: $publishedFrom,
            publishedUntil: $publishedUntil,
            sortOrder: SortOrder::Desc,
            overviewMode: OverviewMode::Group,
        );

        $this->assertSame(
            [
                'title' => 'Gallery 2025',
                'description' => 'Description',
                'cover' => 'cover.jpg',
                'hideCoverInGallery' => false,
                'publishedFrom' => $publishedFrom->getTimestamp(),
                'publishedUntil' => $publishedUntil->getTimestamp(),
                'sortOrder' => SortOrder::Desc->value,
                'overviewMode' => OverviewMode::Group->value,
            ],
            $metadata->getCurrentRecord(),
        );
    }
}
