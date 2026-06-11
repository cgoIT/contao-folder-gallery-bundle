<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2026, cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

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

        $this->assertSame('Friday', $metadata->title);
        $this->assertSame('Test', $metadata->description);
        $this->assertSame('image.jpg', $metadata->cover);
        $this->assertSame('2025-09-05 20:00:00', $metadata->publishedFrom->format('Y-m-d H:i:s'));
        $this->assertSame('2025-09-10 23:59:59', $metadata->publishedUntil->format('Y-m-d H:i:s'));
    }

    public function testReturnsEmptyMetadataIfFileDoesNotExist(): void
    {
        $metadata = $this->reader->read(__DIR__.'/../Fixtures/metadata/empty');

        $this->assertNull($metadata->title);
        $this->assertNull($metadata->description);
        $this->assertNull($metadata->cover);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedFrom);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedUntil);
    }
}
