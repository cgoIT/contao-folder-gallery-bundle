<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Factory;

use Cgoit\ContaoFolderGalleryBundle\Factory\OverviewFolderFlattener;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use PHPUnit\Framework\TestCase;

final class OverviewFolderFlattenerTest extends TestCase
{
    private OverviewFolderFlattener $flattener;

    protected function setUp(): void
    {
        $this->flattener = new OverviewFolderFlattener();
    }

    public function testReturnsFoldersUnchangedIfNothingIsHidden(): void
    {
        $folderA = $this->createFolder('A');
        $folderB = $this->createFolder('B');

        $result = $this->flattener->flatten([$folderA, $folderB]);

        $this->assertSame([$folderA, $folderB], $result);
    }

    public function testFlattensHiddenFolder(): void
    {
        $childA = $this->createFolder('Child A');
        $childB = $this->createFolder('Child B');
        $hidden = $this->createFolder('Hidden', true, [$childA, $childB]);

        $result = $this->flattener->flatten([$hidden]);

        $this->assertSame([$childA, $childB], $result);
    }

    public function testFlattensMixedStructure(): void
    {
        $visible = $this->createFolder('Visible');
        $child = $this->createFolder('Child');
        $hidden = $this->createFolder('Hidden', true, [$child]);

        $result = $this->flattener->flatten([$visible, $hidden]);

        $this->assertSame([$visible, $child], $result);
    }

    public function testFallsBackIfEverythingIsHidden(): void
    {
        $hiddenA = $this->createFolder('A', true);
        $hiddenB = $this->createFolder('B', true);

        $result = $this->flattener->flatten([$hiddenA, $hiddenB]);

        $this->assertSame([$hiddenA, $hiddenB], $result);
    }

    public function testFlattensNestedHiddenFolders(): void
    {
        $visibleChild = $this->createFolder('Friday');
        $hiddenLevel2 = $this->createFolder('Event Group', true, [$visibleChild]);
        $hiddenLevel1 = $this->createFolder('2025', true, [$hiddenLevel2]);

        $result = $this->flattener->flatten([$hiddenLevel1]);

        $this->assertSame([$visibleChild], $result);
    }

    public function testKeepsOrderWhenFlatteningNestedHiddenFolders(): void
    {
        $friday = $this->createFolder('Friday');
        $saturday = $this->createFolder('Saturday');
        $hiddenLevel2 = $this->createFolder('Event Group', true, [$friday, $saturday]);
        $hiddenLevel1 = $this->createFolder('2025', true, [$hiddenLevel2]);

        $result = $this->flattener->flatten([$hiddenLevel1]);

        $this->assertSame([$friday, $saturday], $result);
    }

    /**
     * @param list<GalleryFolder> $children
     */
    private function createFolder(string $title, bool $hiddenInOverview = false, array $children = []): GalleryFolder
    {
        return new GalleryFolder(
            slug: strtolower(str_replace(' ', '-', $title)),
            title: $title,
            trail: [$title],
            metadata: new GalleryMetadata(
                overviewMode: $hiddenInOverview ? OverviewMode::Hidden : OverviewMode::Gallery,
            ),
            folders: $children,
            images: [],
        );
    }
}
