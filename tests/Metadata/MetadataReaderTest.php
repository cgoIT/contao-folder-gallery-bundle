<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Metadata;

use Cgoit\ContaoFolderGalleryBundle\Metadata\MetadataReader;
use PHPUnit\Framework\TestCase;

final class MetadataReaderTest extends TestCase
{
    private MetadataReader $reader;

    protected function setUp(): void
    {
        $this->reader = new MetadataReader();
    }

    public function testReadsValidMetadata(): void
    {
        $metadata = $this->reader->read(__DIR__.'/../Fixtures/metadata/valid');

        self::assertSame('Friday', $metadata->title);
        self::assertSame('Test', $metadata->description);
        self::assertSame('image.jpg', $metadata->cover);
        self::assertSame('2025-09-05 20:00:00', $metadata->publishedFrom->format('Y-m-d H:i:s'));
        self::assertSame('2025-09-10 23:59:59', $metadata->publishedUntil->format('Y-m-d H:i:s'));
    }

    public function testReturnsEmptyMetadataIfFileDoesNotExist(): void
    {
        $metadata = $this->reader->read(__DIR__.'/../Fixtures/metadata/empty');

        self::assertNull($metadata->title);
        self::assertNull($metadata->description);
        self::assertNull($metadata->cover);
        self::assertNull($metadata->publishedFrom);
        self::assertNull($metadata->publishedUntil);
    }
}
