<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;

final class OverviewFolderFlattener
{
    /**
     * @param list<GalleryFolder> $folders
     *
     * @return list<GalleryFolder>
     */
    public function flatten(array $folders): array
    {
        $visibleFolders = $this->doFlatten($folders);

        if ([] === $visibleFolders) {
            $visibleFolders = $folders;
        }

        return $visibleFolders;
    }

    /**
     * @param list<GalleryFolder> $folders
     *
     * @return list<GalleryFolder>
     */
    private function doFlatten(array $folders): array
    {
        $result = [];

        foreach ($folders as $folder) {
            if ($folder->isTransparentInOverview()) {
                $result = [
                    ...$result,
                    ...$this->doFlatten($folder->folders),
                ];

                continue;
            }

            $result[] = $folder;
        }

        return $result;
    }
}
