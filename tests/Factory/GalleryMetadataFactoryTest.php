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

use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryMetadataFactory;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryMetadataFactory::class)]
final class GalleryMetadataFactoryTest extends TestCase
{
    private GalleryMetadataFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new GalleryMetadataFactory();
    }

    public function testCreatesMetadataFromArray(): void
    {
        $metadata = $this->factory->create(
            [
                'title' => 'My gallery',
                'description' => '&lt;p&gt;Hello&lt;/p&gt;',
                'cover' => 'cover.jpg',
                'publishedFrom' => '03.07.2026 14:28',
                'publishedUntil' => '04.07.2026 18:30',
                'sortOrder' => 'desc',
                'overviewMode' => 'group',
            ],
            new \DateTimeZone('Europe/Berlin'),
        );

        $this->assertSame('My gallery', $metadata->title);
        $this->assertSame('<p>Hello</p>', $metadata->description);
        $this->assertSame('cover.jpg', $metadata->cover);

        $this->assertInstanceOf(\DateTimeImmutable::class, $metadata->publishedFrom);
        $this->assertSame('03.07.2026 14:28', $metadata->publishedFrom->format('d.m.Y H:i'));

        $this->assertInstanceOf(\DateTimeImmutable::class, $metadata->publishedUntil);
        $this->assertSame('04.07.2026 18:30', $metadata->publishedUntil->format('d.m.Y H:i'));

        $this->assertSame(SortOrder::Desc, $metadata->sortOrder);
        $this->assertSame(OverviewMode::Group, $metadata->overviewMode);
    }

    public function testUsesDefaults(): void
    {
        $metadata = $this->factory->create([]);

        $this->assertNull($metadata->title);
        $this->assertNull($metadata->description);
        $this->assertNull($metadata->cover);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedFrom);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedUntil);

        $this->assertSame(SortOrder::Asc, $metadata->sortOrder);
        $this->assertSame(OverviewMode::Gallery, $metadata->overviewMode);
    }

    public function testConvertsEmptyStringsToNull(): void
    {
        $metadata = $this->factory->create([
            'title' => '',
            'description' => '',
            'cover' => '',
            'publishedFrom' => '',
            'publishedUntil' => '',
        ]);

        $this->assertNull($metadata->title);
        $this->assertNull($metadata->description);
        $this->assertNull($metadata->cover);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedFrom);
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $metadata->publishedUntil);
    }

    public function testDecodesHtmlEntities(): void
    {
        $metadata = $this->factory->create([
            'description' => '&lt;p&gt;&uuml;&lt;/p&gt;',
        ]);

        $this->assertSame('<p>ü</p>', $metadata->description);
    }
}
