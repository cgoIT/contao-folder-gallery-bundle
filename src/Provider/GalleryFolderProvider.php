<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Repository\GalleryRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(GalleryFolderProviderInterface::class)]
final readonly class GalleryFolderProvider implements GalleryFolderProviderInterface
{
    public function __construct(
        private GalleryRootProviderInterface $rootProvider,
        private GalleryRepositoryInterface $repository,
    ) {
    }

    /**
     * @return list<GalleryFolder>
     */
    public function findAllFolders(): array
    {
        return $this->doFind(true, false);
    }

    /**
     * @return list<GalleryFolder>
     */
    public function findFolderTree(bool $blnShowUnpublished = false): array
    {
        return $this->doFind(false, $blnShowUnpublished);
    }

    /**
     * @return list<GalleryFolder>
     */
    private function doFind(bool $flatten, bool $blnShowUnpublished): array
    {
        $folders = [];

        foreach ($this->rootProvider->getGalleryRoots() as $root) {
            $overview = $this->repository->findOverview($root, $blnShowUnpublished);

            if ($flatten) {
                foreach ($overview->folders as $folder) {
                    $this->collectFolders($folder, $folders);
                }
            } else {
                $folders = [...$folders, ...$overview->folders];
            }
        }

        return $folders;
    }

    /**
     * @param list<GalleryFolder> $folders
     */
    private function collectFolders(GalleryFolder $folder, array &$folders): void
    {
        $folders[] = $folder;

        foreach ($folder->folders as $child) {
            $this->collectFolders($child, $folders);
        }
    }
}
