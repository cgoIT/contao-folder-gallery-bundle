<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\EventListener\DataContainer;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\Controller\FrontendModule\FolderGalleryModule;
use Cgoit\ContaoFolderGalleryBundle\EventListener\DataContainer\ModuleCallbacks;
use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\ImageSizes;
use Contao\CoreBundle\Twig\Finder\FinderFactory;
use Contao\DataContainer;
use Contao\Message;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ModuleCallbacks::class)]
final class ModuleCallbacksTest extends ContaoTestCase
{
    public function testReturnsEmptyArrayIfUserIsNotBackendUser(): void
    {
        $security = $this->createMock(Security::class);
        $security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $imageSizes = $this->createMock(ImageSizes::class);
        $imageSizes
            ->expects($this->never())
            ->method('getOptionsForUser')
        ;

        $galleryCacheInvalidator = $this->createMock(GalleryCacheInvalidator::class);
        $galleryCacheInvalidator
            ->expects($this->never())
            ->method('invalidate')
        ;

        $callbacks = new ModuleCallbacks(
            $this->createStub(FinderFactory::class),
            $security,
            $imageSizes,
            $galleryCacheInvalidator,
            $this->createStub(ContaoFramework::class),
            $this->createStub(TranslatorInterface::class),
        );

        $this->assertSame([], $callbacks->getImageSizes());
    }

    public function testReturnsImageSizesForBackendUser(): void
    {
        $user = $this->createStub(BackendUser::class);

        $security = $this->createMock(Security::class);
        $security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $imageSizes = $this->createMock(ImageSizes::class);
        $imageSizes
            ->expects($this->once())
            ->method('getOptionsForUser')
            ->with($user)
            ->willReturn([
                'small',
                'large',
            ])
        ;

        $galleryCacheInvalidator = $this->createMock(GalleryCacheInvalidator::class);
        $galleryCacheInvalidator
            ->expects($this->never())
            ->method('invalidate')
        ;

        $callbacks = new ModuleCallbacks(
            $this->createStub(FinderFactory::class),
            $security,
            $imageSizes,
            $galleryCacheInvalidator,
            $this->createStub(ContaoFramework::class),
            $this->createStub(TranslatorInterface::class),
        );

        $this->assertSame(['small', 'large'], $callbacks->getImageSizes());
    }

    public function testAddsHintIfModuleHasNoLightboxSize(): void
    {
        $message = $this->mockAdapter(['addInfo']);
        $message
            ->expects($this->once())
            ->method('addInfo')
            ->with('Missing')
        ;

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('tl_module.galleryLightboxSizeMissing', [], 'contao_tl_module')
            ->willReturn('Missing')
        ;

        $callbacks = $this->createCallbacks($message, $translator);
        $callbacks->onFolderGalleryModuleSaved($this->mockDataContainer(['galleryViewer' => 'photoswipe', 'galleryLightboxSize' => serialize(['', '', ''])]));
    }

    /**
     * @param array<string, mixed> $record
     */
    #[DataProvider('getRecordsWithoutHint')]
    public function testDoesNotAddHint(array $record): void
    {
        $message = $this->mockAdapter(['addInfo']);
        $message
            ->expects($this->never())
            ->method('addInfo')
        ;

        $callbacks = $this->createCallbacks($message, $this->createStub(TranslatorInterface::class));
        $callbacks->onFolderGalleryModuleSaved($this->mockDataContainer($record));
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function getRecordsWithoutHint(): iterable
    {
        yield 'no viewer' => [['galleryViewer' => 'none', 'galleryLightboxSize' => '']];
        yield 'module size' => [['galleryViewer' => 'lightbox', 'galleryLightboxSize' => serialize(['1600', '1600', 'box'])]];
        yield 'predefined module size' => [['galleryViewer' => 'lightbox', 'galleryLightboxSize' => serialize(['', '', '_large'])]];
        yield 'other module type' => [['type' => 'navigation', 'galleryViewer' => 'photoswipe', 'galleryLightboxSize' => '']];
    }

    private function createCallbacks(object $message, TranslatorInterface $translator): ModuleCallbacks
    {
        return new ModuleCallbacks(
            $this->createStub(FinderFactory::class),
            $this->createStub(Security::class),
            $this->createStub(ImageSizes::class),
            $this->createStub(GalleryCacheInvalidator::class),
            $this->stubContaoFramework([Message::class => $message]),
            $translator,
        );
    }

    /**
     * @param array<string, mixed> $record
     */
    private function mockDataContainer(array $record): DataContainer
    {
        $dc = $this->createStub(DataContainer::class);
        $dc
            ->method('getCurrentRecord')
            ->willReturn([...['type' => FolderGalleryModule::TYPE], ...$record])
        ;

        return $dc;
    }

    /**
     * @param array<class-string, object> $adapters
     */
    private function stubContaoFramework(array $adapters): ContaoFramework
    {
        $framework = $this->createStub(ContaoFramework::class);
        $framework
            ->method('getAdapter')
            ->willReturnCallback(static fn (string $class): object|null => $adapters[$class] ?? null)
        ;

        return $framework;
    }
}
