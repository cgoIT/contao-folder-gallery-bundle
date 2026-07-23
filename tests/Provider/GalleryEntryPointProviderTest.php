<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Provider;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryEntryPoint;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryEntryPointProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\LayoutModel;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(GalleryEntryPointProvider::class)]
#[UsesClass(GalleryEntryPoint::class)]
#[UsesClass(GalleryRoot::class)]
final class GalleryEntryPointProviderTest extends ContaoTestCase
{
    public function testReturnsEmptyArrayIfThereAreNoGalleryRoots(): void
    {
        $provider = new GalleryEntryPointProvider(
            $this->createContaoFrameworkStub(),
            $this->createGalleryRootProvider([]),
        );

        $this->assertSame([], $provider->getEntryPoints());
    }

    public function testFindsEntryPointFromContentElement(): void
    {
        $galleryRoot = new GalleryRoot(
            moduleName: 'Gallery',
            moduleId: 5,
            filesystemDirectory: '/files/gallery',
        );

        $content = $this->createClassWithPropertiesStub(ContentModel::class, [
            'pid' => 10,
        ]);

        $article = $this->createClassWithPropertiesStub(ArticleModel::class, [
            'pid' => 20,
        ]);

        $page = $this->createClassWithPropertiesStub(PageModel::class, [
            'id' => 30,
        ]);

        $module = $this->createClassWithPropertiesStub(ModuleModel::class, [
            'pid' => 7,
        ]);

        $layout = $this->createClassWithPropertiesStub(LayoutModel::class, [
            'id' => 15,
            'modules' => serialize([
                [
                    'mod' => 5,
                ],
            ]),
        ]);

        $contentAdapter = $this->createAdapterMock(['findBy']);
        $contentAdapter
            ->expects($this->once())
            ->method('findBy')
            ->with(
                ['type = ?', 'module = ?'],
                ['module', 5],
            )
            ->willReturn(
                new Collection([$content], ContentModel::getTable()),
            )
        ;

        $articleAdapter = $this->createAdapterMock(['findById']);
        $articleAdapter
            ->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($article)
        ;

        $pageAdapter = $this->createAdapterMock(['findById', 'findBy']);
        $pageAdapter
            ->expects($this->once())
            ->method('findById')
            ->with(20)
            ->willReturn($page)
        ;

        $moduleAdapter = $this->createAdapterMock(['findById']);
        $moduleAdapter
            ->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn($module)
        ;

        $layoutAdapter = $this->createAdapterMock(['findByPid']);
        $layoutAdapter
            ->expects($this->once())
            ->method('findByPid')
            ->with(7)
            ->willReturn(
                new Collection([$layout], LayoutModel::getTable()),
            )
        ;

        $framework = $this->createContaoFrameworkStub([
            ContentModel::class => $contentAdapter,
            ArticleModel::class => $articleAdapter,
            PageModel::class => $pageAdapter,
            ModuleModel::class => $moduleAdapter,
            LayoutModel::class => $layoutAdapter,
        ]);

        $provider = new GalleryEntryPointProvider(
            $framework,
            $this->createGalleryRootProvider([$galleryRoot]),
        );

        $result = $provider->getEntryPoints();

        $this->assertCount(1, $result);
        $this->assertSame($galleryRoot, $result[0]->galleryRoot);
        $this->assertSame($page, $result[0]->page);
    }

    public function testFindsEntryPointFromLayout(): void
    {
        $galleryRoot = new GalleryRoot(
            moduleName: 'Gallery',
            moduleId: 5,
            filesystemDirectory: '/files/gallery',
        );

        $module = $this->createClassWithPropertiesStub(ModuleModel::class, [
            'pid' => 7,
        ]);

        $layout = $this->createClassWithPropertiesStub(LayoutModel::class, [
            'id' => 15,
            'modules' => serialize([
                [
                    'mod' => 5,
                ],
            ]),
        ]);

        $page = $this->createClassWithPropertiesStub(PageModel::class, [
            'id' => 99,
        ]);

        $contentAdapter = $this->createAdapterMock(['findBy']);
        $contentAdapter
            ->expects($this->once())
            ->method('findBy')
            ->with(
                ['type = ?', 'module = ?'],
                ['module', 5],
            )
            ->willReturn(null)
        ;

        $moduleAdapter = $this->createAdapterMock(['findById']);
        $moduleAdapter
            ->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn($module)
        ;

        $layoutAdapter = $this->createAdapterMock(['findByPid']);
        $layoutAdapter
            ->expects($this->once())
            ->method('findByPid')
            ->with(7)
            ->willReturn(
                new Collection([$layout], LayoutModel::getTable()),
            )
        ;

        $pageAdapter = $this->createAdapterMock(['findBy', 'findById']);
        $pageAdapter
            ->expects($this->once())
            ->method('findBy')
            ->with('layout', 15)
            ->willReturn(
                new Collection([$page], PageModel::getTable()),
            )
        ;

        $framework = $this->createContaoFrameworkStub([
            ContentModel::class => $contentAdapter,
            ModuleModel::class => $moduleAdapter,
            LayoutModel::class => $layoutAdapter,
            PageModel::class => $pageAdapter,
        ]);

        $provider = new GalleryEntryPointProvider(
            $framework,
            $this->createGalleryRootProvider([$galleryRoot]),
        );

        $result = $provider->getEntryPoints();

        $this->assertCount(1, $result);
        $this->assertSame($page, $result[0]->page);
    }

    public function testIgnoresLayoutsWithoutGalleryModule(): void
    {
        $galleryRoot = new GalleryRoot(
            moduleName: 'Gallery',
            moduleId: 5,
            filesystemDirectory: '/files/gallery',
        );

        $module = $this->createClassWithPropertiesStub(ModuleModel::class, [
            'pid' => 7,
        ]);

        $layout = $this->createClassWithPropertiesStub(LayoutModel::class, [
            'id' => 15,
            'modules' => serialize([
                [
                    'mod' => 123,
                ],
            ]),
        ]);

        $contentAdapter = $this->createAdapterMock(['findBy']);
        $contentAdapter
            ->expects($this->once())
            ->method('findBy')
            ->with(
                ['type = ?', 'module = ?'],
                ['module', 5],
            )
            ->willReturn(null)
        ;

        $moduleAdapter = $this->createAdapterMock(['findById']);
        $moduleAdapter
            ->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn($module)
        ;

        $layoutAdapter = $this->createAdapterMock(['findByPid']);
        $layoutAdapter
            ->expects($this->once())
            ->method('findByPid')
            ->with(7)
            ->willReturn(
                new Collection([$layout], LayoutModel::getTable()),
            )
        ;

        $pageAdapter = $this->createAdapterMock(['findBy', 'findById']);
        $pageAdapter
            ->expects($this->never())
            ->method('findBy')
        ;

        $framework = $this->createContaoFrameworkStub([
            ContentModel::class => $contentAdapter,
            ModuleModel::class => $moduleAdapter,
            LayoutModel::class => $layoutAdapter,
            PageModel::class => $pageAdapter,
        ]);

        $provider = new GalleryEntryPointProvider(
            $framework,
            $this->createGalleryRootProvider([$galleryRoot]),
        );

        $this->assertSame([], $provider->getEntryPoints());
    }

    public function testReturnsNoLayoutEntryPointsIfModuleCannotBeFound(): void
    {
        $galleryRoot = new GalleryRoot(
            moduleName: 'Gallery',
            moduleId: 5,
            filesystemDirectory: '/files/gallery',
        );

        $contentAdapter = $this->createAdapterMock(['findBy']);
        $contentAdapter
            ->expects($this->once())
            ->method('findBy')
            ->with(
                ['type = ?', 'module = ?'],
                ['module', 5],
            )
            ->willReturn(null)
        ;

        $moduleAdapter = $this->createAdapterMock(['findById']);
        $moduleAdapter
            ->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn(null)
        ;

        $layoutAdapter = $this->createAdapterMock(['findByPid']);
        $layoutAdapter
            ->expects($this->never())
            ->method('findByPid')
        ;

        $framework = $this->createContaoFrameworkStub([
            ContentModel::class => $contentAdapter,
            ModuleModel::class => $moduleAdapter,
            LayoutModel::class => $layoutAdapter,
        ]);

        $provider = new GalleryEntryPointProvider(
            $framework,
            $this->createGalleryRootProvider([$galleryRoot]),
        );

        $this->assertSame([], $provider->getEntryPoints());
    }

    public function testRemovesDuplicateEntryPoints(): void
    {
        $galleryRoot = new GalleryRoot(
            moduleName: 'Gallery',
            moduleId: 5,
            filesystemDirectory: '/files/gallery',
        );

        $content = $this->createClassWithPropertiesStub(ContentModel::class, [
            'pid' => 10,
        ]);

        $article = $this->createClassWithPropertiesStub(ArticleModel::class, [
            'pid' => 20,
        ]);

        $page = $this->createClassWithPropertiesStub(PageModel::class, [
            'id' => 30,
            'layout' => 15,
        ]);

        $module = $this->createClassWithPropertiesStub(ModuleModel::class, [
            'pid' => 7,
        ]);

        $layout = $this->createClassWithPropertiesStub(LayoutModel::class, [
            'id' => 15,
            'modules' => serialize([
                [
                    'mod' => 5,
                ],
            ]),
        ]);

        $contentAdapter = $this->createConfiguredAdapterStub([
            'findBy' => new Collection([$content], ContentModel::getTable()),
        ]);

        $articleAdapter = $this->createConfiguredAdapterStub([
            'findById' => $article,
        ]);

        $moduleAdapter = $this->createConfiguredAdapterStub([
            'findById' => $module,
        ]);

        $layoutAdapter = $this->createConfiguredAdapterStub([
            'findByPid' => new Collection([$layout], LayoutModel::getTable()),
        ]);

        $pageAdapter = $this->createAdapterStub(['findById', 'findBy']);
        $pageAdapter
            ->method('findById')
            ->willReturn($page)
        ;

        $pageAdapter
            ->method('findBy')
            ->willReturn(
                new Collection([$page], PageModel::getTable()),
            )
        ;

        $framework = $this->createContaoFrameworkStub([
            ContentModel::class => $contentAdapter,
            ArticleModel::class => $articleAdapter,
            ModuleModel::class => $moduleAdapter,
            LayoutModel::class => $layoutAdapter,
            PageModel::class => $pageAdapter,
        ]);

        $provider = new GalleryEntryPointProvider(
            $framework,
            $this->createGalleryRootProvider([$galleryRoot]),
        );

        $result = $provider->getEntryPoints();

        $this->assertCount(1, $result);
        $this->assertSame($galleryRoot, $result[0]->galleryRoot);
        $this->assertSame($page, $result[0]->page);
    }

    /**
     * @param list<GalleryRoot> $galleryRoots
     */
    private function createGalleryRootProvider(array $galleryRoots): GalleryRootProviderInterface
    {
        $provider = $this->createStub(GalleryRootProviderInterface::class);
        $provider
            ->method('getGalleryRoots')
            ->willReturn($galleryRoots)
        ;

        return $provider;
    }
}
