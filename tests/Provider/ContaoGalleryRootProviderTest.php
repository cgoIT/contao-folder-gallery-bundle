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

use Cgoit\ContaoFolderGalleryBundle\FrontendModule\FolderGalleryModule;
use Cgoit\ContaoFolderGalleryBundle\Provider\ContaoGalleryRootProvider;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ContaoGalleryRootProvider::class)]
#[UsesClass(FolderGalleryModule::class)]
final class ContaoGalleryRootProviderTest extends ContaoTestCase
{
    public function testReturnsEmptyArrayIfNoModulesExist(): void
    {
        $moduleAdapter = $this->createAdapterMock(['findBy']);
        $moduleAdapter
            ->expects($this->once())
            ->method('findBy')
            ->with('type', FolderGalleryModule::TYPE)
            ->willReturn(null)
        ;

        $framework = $this->createContaoFrameworkStub([ModuleModel::class => $moduleAdapter]);

        $provider = new ContaoGalleryRootProvider($framework);

        $this->assertSame([], $provider->getGalleryRoots());
    }

    public function testReturnsGalleryRoots(): void
    {
        $moduleA = $this->createClassWithPropertiesStub(ModuleModel::class, ['galleryRoot' => 1]);
        $moduleB = $this->createClassWithPropertiesStub(ModuleModel::class, ['galleryRoot' => 2]);
        $moduleAdapter = $this->createAdapterMock(['findBy']);
        $moduleAdapter
            ->expects($this->once())
            ->method('findBy')
            ->with('type', FolderGalleryModule::TYPE)
            ->willReturn([$moduleA, $moduleB])
        ;

        $fileAdapter = $this->createAdapterMock(['findById']);
        $fileAdapter
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnCallback(
                fn (int $id) => match ($id) {
                    1 => $this->createClassWithPropertiesStub(FilesModel::class, [
                        'path' => 'files/gallery/2025',
                    ]),
                    2 => $this->createClassWithPropertiesStub(FilesModel::class, [
                        'path' => 'files/gallery/2026',
                    ]),
                    default => null,
                },
            )
        ;

        $framework = $this->createContaoFrameworkStub([
            ModuleModel::class => $moduleAdapter,
            FilesModel::class => $fileAdapter,
        ]);

        $provider = new ContaoGalleryRootProvider($framework);

        $this->assertSame(
            [
                'files/gallery/2025',
                'files/gallery/2026',
            ],
            $provider->getGalleryRoots(),
        );
    }

    public function testFiltersInvalidRoots(): void
    {
        $moduleA = $this->createClassWithPropertiesStub(ModuleModel::class, ['galleryRoot' => 1]);
        $moduleB = $this->createClassWithPropertiesStub(ModuleModel::class, ['galleryRoot' => 2]);
        $moduleAdapter = $this->createConfiguredAdapterStub(['findBy' => [$moduleA, $moduleB]]);

        $fileAdapter = $this->createAdapterMock(['findById']);
        $fileAdapter
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnCallback(
                fn (int $id) => match ($id) {
                    1 => $this->createClassWithPropertiesStub(FilesModel::class, [
                        'path' => 'files/gallery',
                    ]),
                    default => null,
                },
            )
        ;

        $framework = $this->createContaoFrameworkStub([
            ModuleModel::class => $moduleAdapter,
            FilesModel::class => $fileAdapter,
        ]);

        $provider = new ContaoGalleryRootProvider($framework);

        $this->assertSame(['files/gallery'], $provider->getGalleryRoots());
    }

    public function testReturnsUniqueRootsOnly(): void
    {
        $moduleA = $this->createClassWithPropertiesStub(ModuleModel::class, ['galleryRoot' => 1]);
        $moduleB = $this->createClassWithPropertiesStub(ModuleModel::class, ['galleryRoot' => 2]);

        $moduleAdapter = $this->createConfiguredAdapterStub(['findBy' => [$moduleA, $moduleB]]);

        $fileAdapter = $this->createAdapterMock(['findById']);
        $fileAdapter
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturn(
                $this->createClassWithPropertiesStub(FilesModel::class, [
                    'path' => 'files/gallery',
                ]),
            )
        ;

        $framework = $this->createContaoFrameworkStub([
            ModuleModel::class => $moduleAdapter,
            FilesModel::class => $fileAdapter,
        ]);

        $provider = new ContaoGalleryRootProvider($framework);

        $this->assertSame(['files/gallery'], $provider->getGalleryRoots());
    }
}
