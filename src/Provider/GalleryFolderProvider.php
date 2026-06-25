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
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
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
     * @return list<GalleryOverview>
     */
    public function findAllOverviews(bool $blnShowUnpublished = false): array
    {
        $overviews = [];

        foreach ($this->rootProvider->getGalleryRoots() as $root) {
            $overview = $this->repository->findOverview($root, $blnShowUnpublished);

            $overviews = [...$overviews, $overview];
        }

        return $overviews;
    }

    public function findOverviewByRootPath(string $path, bool $blnShowUnpublished = false): GalleryOverview|null
    {
        foreach ($this->rootProvider->getGalleryRoots() as $root) {
            if ($root === $path) {
                return $this->repository->findOverview($root, $blnShowUnpublished);
            }
        }

        return null;
    }

    public function findFolderByPath(string $path, bool $blnShowUnpublished = false): GalleryFolder|null
    {
        foreach ($this->findAllOverviews($blnShowUnpublished) as $overview) {
            if (isset($overview->folderIndex[$path])) {
                return $overview->folderIndex[$path];
            }
        }

        return null;
    }
}
