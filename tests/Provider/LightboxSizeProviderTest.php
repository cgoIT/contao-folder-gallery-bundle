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

use Cgoit\ContaoFolderGalleryBundle\Provider\LightboxSizeProvider;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LightboxSizeProvider::class)]
final class LightboxSizeProviderTest extends ContaoTestCase
{
    /**
     * @param array<mixed>|null $expected
     */
    #[DataProvider('getSizes')]
    public function testNormalizesSizes(mixed $size, array|null $expected): void
    {
        $this->assertSame($expected, LightboxSizeProvider::normalize($size));
    }

    /**
     * @return iterable<string, array{mixed, array<mixed>|null}>
     */
    public static function getSizes(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        yield 'empty widget value' => [serialize(['', '', '']), null];
        yield 'mode only' => [serialize(['', '', 'proportional']), null];
        yield 'width' => [serialize(['1200', '', 'proportional']), ['1200', '', 'proportional']];
        yield 'height' => [serialize(['', '800', 'box']), ['', '800', 'box']];
        yield 'image size id' => [serialize(['', '', '5']), ['', '', '5']];
        yield 'predefined size' => [serialize(['', '', '_large']), ['', '', '_large']];
    }

    public function testPrefersModuleSize(): void
    {
        $pageFinder = $this->createMock(PageFinder::class);
        $pageFinder
            ->expects($this->never())
            ->method('getCurrentPage')
        ;

        $provider = new LightboxSizeProvider($pageFinder, $this->createStub(ContaoFramework::class));

        $this->assertSame(['1600', '1600', 'box'], $provider->getLightboxSize(serialize(['1600', '1600', 'box'])));
    }

    public function testFallsBackToLayoutSize(): void
    {
        $page = $this->createClassWithPropertiesStub(PageModel::class, ['layout' => 3]);
        $layout = $this->createClassWithPropertiesStub(LayoutModel::class, ['lightboxSize' => serialize(['1200', '', 'proportional'])]);

        $pageFinder = $this->createStub(PageFinder::class);
        $pageFinder
            ->method('getCurrentPage')
            ->willReturn($page)
        ;

        $layoutAdapter = $this->mockAdapter(['findById']);
        $layoutAdapter
            ->method('findById')
            ->with(3)
            ->willReturn($layout)
        ;

        $provider = new LightboxSizeProvider(
            $pageFinder,
            $this->stubContaoFramework([LayoutModel::class => $layoutAdapter]),
        );

        $this->assertSame(['1200', '', 'proportional'], $provider->getLightboxSize(serialize(['', '', ''])));
    }

    public function testReturnsNullWithoutPage(): void
    {
        $provider = new LightboxSizeProvider($this->createStub(PageFinder::class), $this->createStub(ContaoFramework::class));

        $this->assertNull($provider->getLightboxSize(null));
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
