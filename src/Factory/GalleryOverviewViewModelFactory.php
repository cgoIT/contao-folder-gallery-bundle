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
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryOverviewViewModel;
use Contao\Image\PictureConfiguration;
use Contao\PageModel;

final readonly class GalleryOverviewViewModelFactory
{
    public function __construct(private GalleryFolderViewModelFactory $folderViewModelFactory)
    {
    }

    /**
     * @param array<mixed>|PictureConfiguration|int|string|null $coverImageSize
     */
    public function create(GalleryOverview $overview, PageModel $page, PictureConfiguration|array|int|string|null $coverImageSize): GalleryOverviewViewModel
    {
        $folders = $this->getVisibleFolders($overview->folders);
        $folders = array_map(
            fn (GalleryFolder $folder) => $this->folderViewModelFactory->create($folder, $page, $coverImageSize),
            $folders,
        );

        return new GalleryOverviewViewModel(
            folders: $folders,
        );
    }

    /**
     * @param list<GalleryFolder> $folders
     *
     * @return list<GalleryFolder>
     */
    private function getVisibleFolders(array $folders): array
    {
        $result = [];

        foreach ($folders as $folder) {
            if (!$folder->isPublished()) {
                continue;
            }

            if ($folder->isTransparentInOverview()) {
                $result = [
                    ...$result,
                    ...$this->getVisibleFolders($folder->folders),
                ];

                continue;
            }

            $result[] = $folder;
        }

        return $result;
    }
}
