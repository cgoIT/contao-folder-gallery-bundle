<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Metadata;

use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataReader;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Cgoit\ContaoFolderGalleryBundle\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;

#[CoversClass(GalleryMetadataReader::class)]
final class GalleryMetadataReaderTest extends TestCase
{
    public function testReadsValidMetadata(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $reader = new GalleryMetadataReader($framework);
        $metadata = $reader->read($this->getFixturesDir().'/metadata/valid');

        $this->assertSame('Friday', $metadata->title);
        $this->assertSame('Test', $metadata->description);
        $this->assertSame('image.jpg', $metadata->cover);
        $this->assertSame('2025-09-05 20:00:00', $metadata->publishedFrom->format('Y-m-d H:i:s'));
        $this->assertSame('2025-09-10 23:59:59', $metadata->publishedUntil->format('Y-m-d H:i:s'));
        $this->assertSame(SortOrder::Desc, $metadata->sortOrder);
        $this->assertSame(OverviewMode::Gallery, $metadata->overviewMode);
    }

    public function testReadsMetadataWithInvalidDates(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $reader = new GalleryMetadataReader($framework);
        $metadata = $reader->read($this->getFixturesDir().'/metadata/invalid');

        $this->assertSame('Friday', $metadata->title);
        $this->assertSame('Test', $metadata->description);
        $this->assertSame('image.jpg', $metadata->cover);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedFrom);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedUntil);
    }

    public function testReturnsEmptyMetadataIfFileDoesNotExist(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $reader = new GalleryMetadataReader($framework);
        $metadata = $reader->read($this->getFixturesDir().'/metadata/empty');

        $this->assertNull($metadata->title);
        $this->assertNull($metadata->description);
        $this->assertNull($metadata->cover);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedFrom);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedUntil);
    }

    public function testReturnsDefaultValuesIfFileDoesNotExist(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $reader = new GalleryMetadataReader($framework);
        $this->assertDefaultMetadata(
            $reader->read($this->getFixturesDir().'/metadata/empty'),
        );
    }

    public function testReturnsPublishedTrueIfDatesHaveCorrespondingValues(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $reader = new GalleryMetadataReader($framework);
        $metadata = $reader->read($this->getFixturesDir().'/metadata/published');

        $this->assertTrue($metadata->isPublished());
    }

    public function testReturnsPublishedFalseIfDatesDoNotHaveCorrespondingValues(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $reader = new GalleryMetadataReader($framework);
        $metadata = $reader->read($this->getFixturesDir().'/metadata/valid');

        $this->assertFalse($metadata->isPublished());
    }

    public function testReadsHiddenInOverview(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $reader = new GalleryMetadataReader($framework);
        $metadata = $reader->read($this->getFixturesDir().'/metadata/hidden');
        $this->assertSame(OverviewMode::Hidden, $metadata->overviewMode);
    }

    public function testReturnsDefaultMetadataIfYamlCannotBeParsed(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('warning')
        ;

        $reader = new GalleryMetadataReader($framework, $logger);

        $this->assertDefaultMetadata(
            $reader->read($this->getFixturesDir().'/metadata/invalid-yaml'),
        );
    }

    public function testReturnsDefaultMetadataIfYamlDoesNotContainArray(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('warning')
        ;

        $reader = new GalleryMetadataReader($framework, $logger);

        $this->assertDefaultMetadata(
            $reader->read($this->getFixturesDir().'/metadata/scalar'),
        );
    }

    public function testLogsUnknownMetadataKeys(): void
    {
        $framework = $this->createContaoFrameworkStub();

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('Unknown gallery metadata keys'),
                $this->arrayHasKey('unknown_keys'),
            )
        ;

        $reader = new GalleryMetadataReader($framework, $logger);

        $reader->read($this->getFixturesDir().'/metadata/unknown-key');
    }

    private function assertDefaultMetadata(GalleryMetadata $metadata): void
    {
        $this->assertNull($metadata->title);
        $this->assertNull($metadata->description);
        $this->assertNull($metadata->cover);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedFrom);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedUntil);
        $this->assertSame(SortOrder::Asc, $metadata->sortOrder);
        $this->assertSame(OverviewMode::Gallery, $metadata->overviewMode);
    }
}
