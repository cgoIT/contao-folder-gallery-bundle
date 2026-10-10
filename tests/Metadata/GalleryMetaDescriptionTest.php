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

use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetaDescription;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryMetaDescription::class)]
final class GalleryMetaDescriptionTest extends TestCase
{
    #[DataProvider('provideEmptyDescriptions')]
    public function testReturnsEmptyStringWithoutText(string|null $html): void
    {
        $this->assertSame('', GalleryMetaDescription::fromHtml($html));
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function provideEmptyDescriptions(): iterable
    {
        yield 'null' => [null];
        yield 'empty' => [''];
        yield 'whitespace' => ["  \n "];
        yield 'only tags' => ['<p></p><br>'];
    }

    public function testStripsTagsAndDecodesEntities(): void
    {
        $this->assertSame(
            'Sommerfest & Spiele: "Fotos" der Klasse 4',
            GalleryMetaDescription::fromHtml('<p>Sommerfest &amp; Spiele:</p><p>&quot;<strong>Fotos</strong>&quot; der&nbsp;Klasse 4</p>'),
        );
    }

    public function testSeparatesBlockElementsAndCollapsesWhitespace(): void
    {
        $this->assertSame(
            'Erste Zeile Zweite Zeile',
            GalleryMetaDescription::fromHtml("<p>Erste Zeile</p>\n\n<p>Zweite   Zeile</p>"),
        );
    }

    public function testKeepsShortTextUntouched(): void
    {
        $this->assertSame('Kurz.', GalleryMetaDescription::fromHtml('<p>Kurz.</p>'));
    }

    public function testShortensLongTextAtWordBoundary(): void
    {
        $result = GalleryMetaDescription::fromHtml('<p>'.str_repeat('Wort ', 60).'</p>');

        $this->assertLessThanOrEqual(GalleryMetaDescription::MAX_LENGTH, mb_strlen($result));
        $this->assertStringEndsWith('Wort…', $result);
    }

    public function testShortensTextWithoutSpaces(): void
    {
        $result = GalleryMetaDescription::fromHtml(str_repeat('ä', 300), 50);

        $this->assertSame(50, mb_strlen($result));
        $this->assertStringEndsWith('…', $result);
    }
}
