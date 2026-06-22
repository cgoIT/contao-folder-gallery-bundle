<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Matcher;

use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Contao\CoreBundle\Filesystem\Dbafs\ChangeSet\ChangeSet;

final readonly class GalleryPathMatcher
{
    public function __construct(private GalleryRootProviderInterface $rootProvider)
    {
    }

    public function affectsGallery(ChangeSet $changeSet): bool
    {
        $roots = $this->rootProvider->getGalleryRoots();

        foreach ($changeSet->getItemsToCreate() as $item) {
            if ($this->matchesRoot($item->getPath(), $roots)) {
                return true;
            }
        }

        foreach ($changeSet->getItemsToUpdate() as $item) {
            if (
                $this->matchesRoot($item->getExistingPath(), $roots)
                || (
                    $item->updatesPath()
                    && $this->matchesRoot($item->getNewPath(), $roots)
                )
            ) {
                return true;
            }
        }

        foreach ($changeSet->getItemsToDelete() as $item) {
            if ($this->matchesRoot($item->getPath(), $roots)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string> $roots
     */
    private function matchesRoot(string $path, array $roots): bool
    {
        foreach ($roots as $root) {
            if (str_starts_with($path, $root.'/')) {
                return true;
            }

            if ($path === $root) {
                return true;
            }
        }

        return false;
    }
}
