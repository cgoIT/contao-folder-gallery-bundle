<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\EventListener;

use Cgoit\ContaoFolderGalleryBundle\EventListener\GallerySitemapListener;
use Cgoit\ContaoFolderGalleryBundle\Model\SitemapEntry;
use Cgoit\ContaoFolderGalleryBundle\Provider\GallerySitemapProviderInterface;
use Contao\CoreBundle\Event\SitemapEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GallerySitemapListener::class)]
#[UsesClass(SitemapEntry::class)]
final class GallerySitemapListenerTest extends TestCase
{
    public function testAddsAllUrlsToSitemapEvent(): void
    {
        $provider = $this->createMock(GallerySitemapProviderInterface::class);
        $provider
            ->expects($this->once())
            ->method('getEntries')
            ->willReturn([
                new SitemapEntry('https://example.com/gallery/2024'),
                new SitemapEntry('https://example.com/gallery/2025'),
            ])
        ;

        $urls = [];

        $event = $this->createMock(SitemapEvent::class);
        $event
            ->expects($this->exactly(2))
            ->method('addUrlToDefaultUrlSet')
            ->willReturnCallback(
                static function (string $url) use (&$urls, $event): SitemapEvent {
                    $urls[] = $url;

                    return $event;
                },
            )
        ;

        $listener = new GallerySitemapListener($provider);
        $listener($event);

        $this->assertSame(
            [
                'https://example.com/gallery/2024',
                'https://example.com/gallery/2025',
            ],
            $urls,
        );
    }

    public function testDoesNothingIfThereAreNoEntries(): void
    {
        $provider = $this->createStub(GallerySitemapProviderInterface::class);
        $provider
            ->method('getEntries')
            ->willReturn([])
        ;

        $event = $this->createMock(SitemapEvent::class);
        $event
            ->expects($this->never())
            ->method('addUrlToDefaultUrlSet')
        ;

        $listener = new GallerySitemapListener($provider);

        $listener($event);
    }
}
