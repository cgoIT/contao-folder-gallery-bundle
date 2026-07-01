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
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataWriter;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Cgoit\ContaoFolderGalleryBundle\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(GalleryMetadataWriter::class)]
final class GalleryMetadataWriterTest extends TestCase
{
    private string $tempDirectory;

    private GalleryMetadataWriter $writer;

    protected function setUp(): void
    {
        $this->tempDirectory = sys_get_temp_dir().'/gallery-metadata-writer-'.uniqid('', true);

        (new Filesystem())->mkdir($this->tempDirectory);

        $this->writer = new GalleryMetadataWriter(new Filesystem());
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tempDirectory);

        parent::tearDown();
    }

    public function testWritesMetadata(): void
    {
        $metadata = new GalleryMetadata(
            title: 'Friday',
            description: 'Test',
            cover: 'image.jpg',
            publishedFrom: new \DateTimeImmutable('2025-09-05 20:00:00'),
            publishedUntil: new \DateTimeImmutable('2025-09-10 23:59:59'),
            sortOrder: SortOrder::Desc,
            overviewMode: OverviewMode::Hidden,
        );

        $this->writer->write($this->tempDirectory, $metadata);

        $data = Yaml::parseFile($this->tempDirectory.'/_metadata.yml');

        $this->assertSame('Friday', $data['title']);
        $this->assertSame('Test', $data['description']);
        $this->assertSame('image.jpg', $data['cover']);
        $this->assertSame('2025-09-05 20:00:00', $data['published_from']);
        $this->assertSame('2025-09-10 23:59:59', $data['published_until']);
        $this->assertSame('desc', $data['sort_order']);
        $this->assertSame('hidden', $data['overview_mode']);
    }

    public function testOmitsNullValues(): void
    {
        $this->writer->write($this->tempDirectory, new GalleryMetadata());

        $data = Yaml::parseFile($this->tempDirectory.'/_metadata.yml');

        $this->assertSame(
            [
                'sort_order' => 'asc',
                'overview_mode' => 'gallery',
            ],
            $data,
        );
    }

    public function testRoundTrip(): void
    {
        $metadata = new GalleryMetadata(
            title: 'Friday',
            description: 'Test',
            cover: 'image.jpg',
            publishedFrom: new \DateTimeImmutable('2025-09-05 20:00:00'),
            publishedUntil: new \DateTimeImmutable('2025-09-10 23:59:59'),
            sortOrder: SortOrder::Desc,
            overviewMode: OverviewMode::Hidden,
        );

        $this->writer->write($this->tempDirectory, $metadata);

        $framework = $this->createContaoFrameworkStub();

        $reader = new GalleryMetadataReader($framework);
        $loaded = $reader->read($this->tempDirectory);

        $this->assertSame($metadata->title, $loaded->title);
        $this->assertSame($metadata->description, $loaded->description);
        $this->assertSame($metadata->cover, $loaded->cover);
        $this->assertSame($metadata->publishedFrom->getTimestamp(), $loaded->publishedFrom?->getTimestamp());
        $this->assertSame($metadata->publishedUntil->getTimestamp(), $loaded->publishedUntil?->getTimestamp());
        $this->assertSame($metadata->sortOrder, $loaded->sortOrder);
        $this->assertSame($metadata->overviewMode, $loaded->overviewMode);
    }
}
