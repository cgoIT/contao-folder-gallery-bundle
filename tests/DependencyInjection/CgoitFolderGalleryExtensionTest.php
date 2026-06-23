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

use Cgoit\ContaoFolderGalleryBundle\DependencyInjection\CgoitFolderGalleryExtension;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFigureFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryFolderViewModelFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryOverviewFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\OverviewFolderFlattener;
use Cgoit\ContaoFolderGalleryBundle\Loader\ContaoGalleryImageLoader;
use Cgoit\ContaoFolderGalleryBundle\Matcher\GalleryPathMatcher;
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataReader;
use Cgoit\ContaoFolderGalleryBundle\Provider\ContaoFilesModelProvider;
use Cgoit\ContaoFolderGalleryBundle\Provider\ContaoGalleryRootProvider;
use Cgoit\ContaoFolderGalleryBundle\Repository\CachedGalleryRepository;
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
        GalleryMetadataReader::class,
        FilesystemGalleryRepository::class,
        ContaoFilesModelProvider::class,
        GalleryFigureFactory::class,
        ContaoGalleryImageLoader::class,
        GalleryFolderViewModelFactory::class,
        GalleryOverviewFactory::class,
        OverviewFolderFlattener::class,
        GalleryPathMatcher::class,
        ContaoGalleryRootProvider::class,
        CachedGalleryRepository::class,
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

        $this->assertSame($serviceId, $definition->getClass());
        $this->assertTrue($definition->isAutowired());
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
