<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\DependencyInjection;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheWarmer;
use Cgoit\ContaoFolderGalleryBundle\Controller\Backend\GalleryBackendController;
use Cgoit\ContaoFolderGalleryBundle\Controller\Backend\GalleryMetadataAjaxHandler;
use Cgoit\ContaoFolderGalleryBundle\DependencyInjection\CgoitFolderGalleryExtension;
use Cgoit\ContaoFolderGalleryBundle\Drivers\DC_GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\EventListener\DataContainer\ModuleCallbacks;
use Cgoit\ContaoFolderGalleryBundle\EventListener\GalleryCacheInvalidateListener;
use Cgoit\ContaoFolderGalleryBundle\EventListener\Menu\BackendFolderGalleryListener;
use Cgoit\ContaoFolderGalleryBundle\EventSubscriber\AddBackendAssetsSubscriber;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryContentFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFigureFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFolderViewModelFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryMetadataFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryOverviewViewModelFactory;
use Cgoit\ContaoFolderGalleryBundle\FrontendModule\FolderGalleryModule;
use Cgoit\ContaoFolderGalleryBundle\Loader\ContaoGalleryImageLoader;
use Cgoit\ContaoFolderGalleryBundle\Matcher\GalleryPathMatcher;
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataManager;
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataReader;
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataWriter;
use Cgoit\ContaoFolderGalleryBundle\Provider\CachedGalleryFolderProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\ContaoFilesModelProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\ContaoGalleryRootProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryEntryPointProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFolderProvider;
use Cgoit\ContaoFolderGalleryBundle\Repository\FilesystemGalleryRepository;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(CgoitFolderGalleryExtension::class)]
final class CgoitFolderGalleryExtensionTest extends TestCase
{
    private const array SERVICES = [
        GalleryCacheInvalidator::class,
        GalleryCacheWarmer::class,

        GalleryBackendController::class,
        GalleryMetadataAjaxHandler::class,

        DC_GalleryMetadata::class,

        ModuleCallbacks::class,
        BackendFolderGalleryListener::class,
        GalleryCacheInvalidateListener::class,

        AddBackendAssetsSubscriber::class,

        GalleryContentFactory::class,
        GalleryFigureFactory::class,
        GalleryFolderViewModelFactory::class,
        GalleryMetadataFactory::class,
        GalleryOverviewViewModelFactory::class,

        FolderGalleryModule::class,

        ContaoGalleryImageLoader::class,

        GalleryPathMatcher::class,

        GalleryMetadataManager::class,
        GalleryMetadataReader::class,
        GalleryMetadataWriter::class,

        CachedGalleryFolderProvider::class,
        ContaoFilesModelProvider::class,
        ContaoGalleryRootProvider::class,
        GalleryFolderProvider::class,
        GalleryEntryPointProvider::class,

        FilesystemGalleryRepository::class,

        GalleryUrlGenerator::class,
    ];

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $extension = new CgoitFolderGalleryExtension();

        $this->container = new ContainerBuilder();

        $extension->load([], $this->container);
    }

    #[DataProvider('serviceProvider')]
    public function testExtensionLoadsServices(string $serviceId): void
    {
        $this->assertTrue(
            $this->container->hasDefinition($serviceId),
            \sprintf(
                'Service "%s" was not registered by the extension.',
                $serviceId,
            ),
        );

        $definition = $this->container->getDefinition($serviceId);

        $this->assertTrue($definition->isAutowired());
        $this->assertFalse($definition->isAbstract());
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function serviceProvider(): iterable
    {
        foreach (self::SERVICES as $serviceId) {
            yield $serviceId => [$serviceId];
        }
    }
}
