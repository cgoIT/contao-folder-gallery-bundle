<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Controller\FrontendModule;

use Cgoit\ContaoFolderGalleryBundle\Controller\FrontendModule\FolderGalleryModule;
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetaDescription;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(FolderGalleryModule::class)]
#[UsesClass(GalleryMetaDescription::class)]
#[UsesClass(GalleryFolder::class)]
#[UsesClass(GalleryMetadata::class)]
final class FolderGalleryModuleTest extends ContaoTestCase
{
    public function testUsesTheGalleryTitleAndDescriptionForTheHtmlHeadBag(): void
    {
        $htmlHeadBag = new HtmlHeadBag();
        $htmlHeadBag->setTitle('Page title');
        $htmlHeadBag->setMetaDescription('Page description');

        $this->updateHtmlHeadBag(
            $htmlHeadBag,
            $this->createFolder('Sommerfest', '<p>Fotos vom <strong>Sommerfest</strong></p>'),
        );

        $this->assertSame('Sommerfest', $htmlHeadBag->getTitle());
        $this->assertSame('Fotos vom Sommerfest', $htmlHeadBag->getMetaDescription());
    }

    public function testKeepsThePageDescriptionWithoutGalleryDescription(): void
    {
        $htmlHeadBag = new HtmlHeadBag();
        $htmlHeadBag->setTitle('Page title');
        $htmlHeadBag->setMetaDescription('Page description');

        $this->updateHtmlHeadBag($htmlHeadBag, $this->createFolder('Sommerfest', null));

        $this->assertSame('Sommerfest', $htmlHeadBag->getTitle());
        $this->assertSame('Page description', $htmlHeadBag->getMetaDescription());
    }

    public function testKeepsThePageTitleWithoutGalleryTitle(): void
    {
        $htmlHeadBag = new HtmlHeadBag();
        $htmlHeadBag->setTitle('Page title');

        $this->updateHtmlHeadBag($htmlHeadBag, $this->createFolder(' ', null));

        $this->assertSame('Page title', $htmlHeadBag->getTitle());
    }

    public function testDoesNothingWithoutResponseContext(): void
    {
        $module = $this->createModule(null);

        $method = new \ReflectionMethod($module, 'updateHtmlHeadBag');
        $method->invoke($module, $this->createFolder('Sommerfest', null));

        $this->addToAssertionCount(1);
    }

    private function updateHtmlHeadBag(HtmlHeadBag $htmlHeadBag, GalleryFolder $folder): void
    {
        $responseContext = new ResponseContext();
        $responseContext->add($htmlHeadBag);

        $module = $this->createModule($responseContext);

        $method = new \ReflectionMethod($module, 'updateHtmlHeadBag');
        $method->invoke($module, $folder);
    }

    private function createModule(ResponseContext|null $responseContext): FolderGalleryModule
    {
        $accessor = new ResponseContextAccessor(new RequestStack([new Request()]));

        if (null !== $responseContext) {
            $accessor->setResponseContext($responseContext);
        }

        $container = new Container();
        $container->set('contao.routing.response_context_accessor', $accessor);

        // The factories are final and the dependencies are not needed to update the head bag
        $module = (new \ReflectionClass(FolderGalleryModule::class))->newInstanceWithoutConstructor();
        $module->setContainer($container);

        return $module;
    }

    private function createFolder(string $title, string|null $description): GalleryFolder
    {
        return new GalleryFolder(
            slug: 'folder',
            title: $title,
            filesystemDirectory: 'files/gallery/folder',
            trail: ['folder'],
            metadata: new GalleryMetadata(description: $description),
        );
    }
}
