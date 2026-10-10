<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Security;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Security\GalleryBackendAccess;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

#[CoversClass(GalleryBackendAccess::class)]
final class GalleryBackendAccessTest extends TestCase
{
    public function testDeniesModuleWithoutPermission(): void
    {
        $access = $this->createAccess(false, []);

        $this->assertFalse($access->canAccessModule());
        $this->assertFalse($access->canEditFolder('files/gallery'));

        $this->expectException(AccessDeniedException::class);

        $access->denyAccessUnlessModuleGranted();
    }

    public function testDeniesFolderOutsideOfFileMounts(): void
    {
        $access = $this->createAccess(true, ['files/gallery/a']);

        $this->assertTrue($access->canEditFolder('files/gallery/a'));
        $this->assertFalse($access->canEditFolder('files/gallery/b'));

        $this->expectException(AccessDeniedException::class);

        $access->denyAccessUnlessFolderGranted('files/gallery/b');
    }

    public function testFilterOverviewsKeepsAncestorsOfAccessibleFolders(): void
    {
        $access = $this->createAccess(true, ['files/gallery/a/deep']);

        $overviews = $access->filterOverviews([$this->createOverview()]);

        $this->assertCount(1, $overviews);
        $this->assertSame(['files/gallery/a'], array_map(static fn (GalleryFolder $f) => $f->filesystemDirectory, $overviews[0]->folders));
        $this->assertSame(
            ['files/gallery/a/deep'],
            array_map(static fn (GalleryFolder $f) => $f->filesystemDirectory, $overviews[0]->folders[0]->folders),
        );
        $this->assertEqualsCanonicalizing(['a', 'a/deep'], array_keys($overviews[0]->folderIndex));
        $this->assertSame(['files/gallery/a/deep' => true], $access->findEditablePaths($overviews));
    }

    public function testFilterOverviewsDropsOverviewWithoutAccessibleFolders(): void
    {
        $access = $this->createAccess(true, ['files/other']);

        $this->assertSame([], $access->filterOverviews([$this->createOverview()]));
    }

    public function testFilterOverviewsReturnsNothingWithoutModulePermission(): void
    {
        $access = $this->createAccess(false, ['files/gallery']);

        $this->assertSame([], $access->filterOverviews([$this->createOverview()]));
    }

    /**
     * @param list<string> $mounts
     */
    private function createAccess(bool $moduleGranted, array $mounts): GalleryBackendAccess
    {
        $security = $this->createStub(Security::class);
        $security
            ->method('isGranted')
            ->willReturnCallback(
                static fn (string $attribute, mixed $subject): bool => match ($attribute) {
                    ContaoCorePermissions::USER_CAN_ACCESS_MODULE => $moduleGranted,
                    ContaoCorePermissions::USER_CAN_ACCESS_PATH => \in_array($subject, $mounts, true),
                    default => false,
                },
            )
        ;

        return new GalleryBackendAccess($security);
    }

    private function createOverview(): GalleryOverview
    {
        $metadata = new GalleryMetadata();

        $deep = new GalleryFolder('deep', 'Deep', 'files/gallery/a/deep', ['a', 'deep'], $metadata);
        $a = new GalleryFolder('a', 'A', 'files/gallery/a', ['a'], $metadata, [$deep]);
        $b = new GalleryFolder('b', 'B', 'files/gallery/b', ['b'], $metadata);

        return new GalleryOverview(
            new GalleryRoot('Gallery', 1, 'files/gallery'),
            [$a, $b],
            ['a' => $a, 'a/deep' => $deep, 'b' => $b],
        );
    }
}
