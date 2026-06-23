<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Routing;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGenerator;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(GalleryUrlGenerator::class)]
#[UsesClass(GalleryFolder::class)]
#[UsesClass(GalleryMetadata::class)]
final class GalleryUrlGeneratorTest extends TestCase
{
    public function testGeneratesFolderUrl(): void
    {
        $page = $this->createStub(PageModel::class);

        $folder = new GalleryFolder(
            slug: 'friday',
            title: 'Friday',
            filesystemDirectory: '/files/gallery/friday',
            trail: ['2025', 'friday'],
            metadata: new GalleryMetadata(),
            folders: [],
            images: [],
        );

        $contentUrlGenerator = $this->createMock(ContentUrlGenerator::class);
        $contentUrlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $page,
                [
                    'parameters' => '/2025/friday',
                ],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            )
            ->willReturn('/gallery/2025/friday')
        ;

        $generator = new GalleryUrlGenerator($contentUrlGenerator);

        $url = $generator->generate($page, $folder);

        $this->assertSame('/gallery/2025/friday', $url);
    }

    public function testPassesReferenceTypeToContentUrlGenerator(): void
    {
        $page = $this->createStub(PageModel::class);

        $folder = new GalleryFolder(
            slug: 'friday',
            title: 'Friday',
            filesystemDirectory: '/files/gallery/friday',
            trail: ['2025', 'friday'],
            metadata: new GalleryMetadata(),
            folders: [],
            images: [],
        );

        $contentUrlGenerator = $this->createMock(ContentUrlGenerator::class);
        $contentUrlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $page,
                [
                    'parameters' => '/2025/friday',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL,
            )
            ->willReturn('https://example.org/gallery/2025/friday')
        ;

        $generator = new GalleryUrlGenerator($contentUrlGenerator);
        $generator->generate($page, $folder, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
