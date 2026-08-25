<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\EventSubscriber;

use Cgoit\ContaoFolderGalleryBundle\EventSubscriber\AddBackendAssetsSubscriber;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(AddBackendAssetsSubscriber::class)]
final class AddBackendAssetsSubscriberTest extends ContaoTestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TL_CSS']);

        parent::tearDown();
    }

    public function testSubscribedEvents(): void
    {
        $this->assertSame(
            [
                KernelEvents::REQUEST => 'onKernelRequest',
            ],
            AddBackendAssetsSubscriber::getSubscribedEvents(),
        );
    }

    public function testAddsBackendCss(): void
    {
        $request = new Request();

        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher
            ->expects($this->once())
            ->method('isBackendRequest')
            ->with($request)
            ->willReturn(true)
        ;

        $packages = $this->createMock(Packages::class);
        $packages
            ->expects($this->once())
            ->method('getUrl')
            ->with('backend-css.css', 'cgoit_folder_gallery')
            ->willReturn('bundles/cgoitfoldergallery/backend-css.css')
        ;

        $subscriber = new AddBackendAssetsSubscriber($scopeMatcher, $packages);

        $subscriber->onKernelRequest(
            $this->createRequestEvent($request),
        );

        $this->assertSame(
            ['bundles/cgoitfoldergallery/backend-css.css'],
            $GLOBALS['TL_CSS'],
        );
    }

    public function testDoesNotAddBackendCssForFrontendRequest(): void
    {
        $request = new Request();

        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher
            ->expects($this->once())
            ->method('isBackendRequest')
            ->with($request)
            ->willReturn(false)
        ;

        $packages = $this->createMock(Packages::class);
        $packages
            ->expects($this->never())
            ->method('getUrl')
            ->with('backend-css.css', 'cgoit_folder_gallery')
            ->willReturn('bundles/cgoitfoldergallery/backend-css.css')
        ;

        $subscriber = new AddBackendAssetsSubscriber($scopeMatcher, $packages);

        $subscriber->onKernelRequest(
            $this->createRequestEvent($request),
        );

        $this->assertArrayNotHasKey(
            'TL_CSS',
            $GLOBALS,
        );
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }
}
