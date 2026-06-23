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

    public function findAllFolders(): array
    {
        $folders = [];

        foreach ($this->rootProvider->getGalleryRoots() as $root) {
            $overview = $this->repository->findOverview($root);

            foreach ($overview->folders as $folder) {
                $this->collectFolders($folder, $folders);
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
