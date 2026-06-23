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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OverviewFolderFlattener::class)]
#[UsesClass(GalleryFolder::class)]
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
        $hidden = $this->createFolder('Hidden', OverviewMode::Hidden, [$childA, $childB]);

        $result = $this->flattener->flatten([$hidden]);

        $this->assertSame([$childA, $childB], $result);
    }

    public function testFlattensMixedStructure(): void
    {
        $visible = $this->createFolder('Visible');
        $child = $this->createFolder('Child');
        $hidden = $this->createFolder('Hidden', OverviewMode::Hidden, [$child]);

        $result = $this->flattener->flatten([$visible, $hidden]);

        $this->assertSame([$visible, $child], $result);
    }

    public function testFallsBackIfEverythingIsHidden(): void
    {
        $hiddenA = $this->createFolder('A', OverviewMode::Hidden);
        $hiddenB = $this->createFolder('B', OverviewMode::Hidden);

        $result = $this->flattener->flatten([$hiddenA, $hiddenB]);

        $this->assertSame([$hiddenA, $hiddenB], $result);
    }

    public function testFlattensNestedHiddenFolders(): void
    {
        $visibleChild = $this->createFolder('Friday');
        $hiddenLevel2 = $this->createFolder('Event Group', OverviewMode::Hidden, [$visibleChild]);
        $hiddenLevel1 = $this->createFolder('2025', OverviewMode::Hidden, [$hiddenLevel2]);

        $result = $this->flattener->flatten([$hiddenLevel1]);

        $this->assertSame([$visibleChild], $result);
    }

    public function testKeepsOrderWhenFlatteningNestedHiddenFolders(): void
    {
        $friday = $this->createFolder('Friday');
        $saturday = $this->createFolder('Saturday');
        $hiddenLevel2 = $this->createFolder('Event Group', OverviewMode::Hidden, [$friday, $saturday]);
        $hiddenLevel1 = $this->createFolder('2025', OverviewMode::Hidden, [$hiddenLevel2]);

        $result = $this->flattener->flatten([$hiddenLevel1]);

        $this->assertSame([$friday, $saturday], $result);
    }

    public function testDoesNotFlattenGroupFolders(): void
    {
        $child = $this->createFolder('Friday');
        $group = $this->createFolder('2025', OverviewMode::Group, [$child]);

        $result = $this->flattener->flatten([$group]);

        $this->assertSame([$group], $result);
    }

    /**
     * @param list<GalleryFolder> $children
     */
    private function createFolder(string $title, OverviewMode $overviewMode = OverviewMode::Gallery, array $children = []): GalleryFolder
    {
        return new GalleryFolder(
            slug: strtolower(str_replace(' ', '-', $title)),
            title: $title,
            trail: [$title],
            metadata: new GalleryMetadata(overviewMode: $overviewMode),
            folders: $children,
            images: [],
        );
    }
}
