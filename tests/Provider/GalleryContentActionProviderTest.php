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

use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentAction;
use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentActionInterface;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryContentActionProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryContentActionProvider::class)]
#[UsesClass(GalleryContentAction::class)]
#[UsesClass(GalleryFolder::class)]
final class GalleryContentActionProviderTest extends TestCase
{
    public function testReturnsProvidedActions(): void
    {
        $folder = new GalleryFolder(
            slug: 'gallery',
            title: 'Gallery',
            filesystemDirectory: '/files/gallery',
            trail: ['gallery'],
            metadata: new GalleryMetadata(),
        );

        $action = new GalleryContentAction(
            label: 'Download',
            url: '/gallery/download',
        );

        $actionProvider = $this->createMock(GalleryContentActionInterface::class);
        $actionProvider
            ->method('createAction')
            ->with($folder)
            ->willReturn($action)
        ;

        $provider = new GalleryContentActionProvider([$actionProvider]);

        $result = $provider->getActions($folder);

        $this->assertCount(1, $result);
        $this->assertSame($action, $result[0]);
    }

    public function testIgnoresNullActions(): void
    {
        $folder = new GalleryFolder(
            slug: 'gallery',
            title: 'Gallery',
            filesystemDirectory: '/files/gallery',
            trail: ['gallery'],
            metadata: new GalleryMetadata(),
        );

        $actionProvider = $this->createMock(GalleryContentActionInterface::class);
        $actionProvider
            ->method('createAction')
            ->with($folder)
            ->willReturn(null)
        ;

        $provider = new GalleryContentActionProvider([$actionProvider]);

        $result = $provider->getActions($folder);

        $this->assertSame([], $result);
    }

    public function testCombinesActionsFromMultipleImplementations(): void
    {
        $folder = new GalleryFolder(
            slug: 'gallery',
            title: 'Gallery',
            filesystemDirectory: '/files/gallery',
            trail: ['gallery'],
            metadata: new GalleryMetadata(),
        );

        $firstAction = new GalleryContentAction(
            label: 'Download',
            url: '/gallery/download',
        );

        $secondAction = new GalleryContentAction(
            label: 'Share',
            url: '/gallery/share',
        );

        $firstProvider = $this->createMock(GalleryContentActionInterface::class);
        $firstProvider
            ->method('createAction')
            ->with($folder)
            ->willReturn($firstAction)
        ;

        $secondProvider = $this->createMock(GalleryContentActionInterface::class);
        $secondProvider
            ->method('createAction')
            ->with($folder)
            ->willReturn($secondAction)
        ;

        $provider = new GalleryContentActionProvider([
            $firstProvider,
            $secondProvider,
        ]);

        $result = $provider->getActions($folder);

        $this->assertCount(2, $result);
        $this->assertSame($firstAction, $result[0]);
        $this->assertSame($secondAction, $result[1]);
    }
}
