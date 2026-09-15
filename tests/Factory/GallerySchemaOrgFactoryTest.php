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

use Cgoit\ContaoFolderGalleryBundle\Factory\GallerySchemaOrgFactory;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\CoreBundle\Asset\ContaoContext;
use Contao\CoreBundle\File\Metadata;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\ImageResult;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\Image\PictureInterface;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;

#[CoversClass(GallerySchemaOrgFactory::class)]
final class GallerySchemaOrgFactoryTest extends ContaoTestCase
{
    public function testBuildsImageGalleryWithIdOnlyImageReferences(): void
    {
        $figureA = new Figure(
            new ImageResult($this->createImageContainerStub('image-a.jpg'), 'project-dir', 'image-a.jpg'),
            new Metadata([Metadata::VALUE_UUID => 'uuid-a']),
        );
        $figureB = new Figure(
            new ImageResult($this->createImageContainerStub('image-b.jpg'), 'project-dir', 'image-b.jpg'),
            new Metadata([Metadata::VALUE_UUID => 'uuid-b']),
        );

        $folder = new GalleryFolderViewModel(
            title: 'Freitag',
            slug: 'freitag',
            url: '/galerie/2025/freitag',
            children: [],
            imageCount: 2,
            galleryCount: 0,
            coverFigure: null,
            description: '<p>Impressionen vom Freitag</p>',
            anchor: null,
            level: 1,
            overviewMode: OverviewMode::Gallery,
        );

        $htmlDecoder = $this->createMock(HtmlDecoder::class);
        $htmlDecoder
            ->method('inputEncodedToPlainText')
            ->with('Freitag')
            ->willReturn('Freitag')
        ;

        $htmlDecoder
            ->method('htmlToPlainText')
            ->with('<p>Impressionen vom Freitag</p>')
            ->willReturn('Impressionen vom Freitag')
        ;

        $factory = new GallerySchemaOrgFactory($htmlDecoder);

        $result = $factory->create($folder, [$figureA, $figureB]);

        $this->assertSame('ImageGallery', $result['@type']);
        $this->assertSame('Freitag', $result['name']);
        $this->assertSame('Impressionen vom Freitag', $result['description']);
        $this->assertSame(
            [
                ['@id' => '#/schema/image/uuid-a'],
                ['@id' => '#/schema/image/uuid-b'],
            ],
            $result['associatedMedia'],
        );
    }

    public function testOmitsDescriptionAndAssociatedMediaWhenNotAvailable(): void
    {
        $folder = new GalleryFolderViewModel(
            title: 'Freitag',
            slug: 'freitag',
            url: '/galerie/2025/freitag',
            children: [],
            imageCount: 0,
            galleryCount: 0,
            coverFigure: null,
            description: null,
            anchor: null,
            level: 1,
            overviewMode: OverviewMode::Gallery,
        );

        $htmlDecoder = $this->createMock(HtmlDecoder::class);
        $htmlDecoder
            ->method('inputEncodedToPlainText')
            ->willReturn('Freitag')
        ;

        $htmlDecoder
            ->expects($this->never())
            ->method('htmlToPlainText')
        ;

        $factory = new GallerySchemaOrgFactory($htmlDecoder);

        $result = $factory->create($folder, []);

        $this->assertArrayNotHasKey('description', $result);
        $this->assertArrayNotHasKey('associatedMedia', $result);
    }

    /**
     * Figure::getSchemaOrgData() unconditionally reads the image URL (for
     * "contentUrl"), so building a real Figure/ImageResult pair for this test
     * needs a container that can resolve the picture pipeline services.
     */
    private function createImageContainerStub(string $imageSrc): ContainerInterface
    {
        $picture = $this->createStub(PictureInterface::class);
        $picture
            ->method('getImg')
            ->willReturn(['src' => $imageSrc])
        ;

        $pictureFactory = $this->createStub(PictureFactoryInterface::class);
        $pictureFactory
            ->method('create')
            ->willReturn($picture)
        ;

        $filesContext = $this->createStub(ContaoContext::class);
        $filesContext
            ->method('getStaticUrl')
            ->willReturn('')
        ;

        $container = $this->createStub(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnMap([
                ['contao.image.picture_factory', $pictureFactory],
                ['contao.assets.files_context', $filesContext],
            ])
        ;

        return $container;
    }
}
