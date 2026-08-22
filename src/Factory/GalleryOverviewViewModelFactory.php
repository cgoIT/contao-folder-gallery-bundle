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
use Contao\ModuleModel;
use Contao\PageModel;

final readonly class GalleryOverviewViewModelFactory
{
    public function __construct(private GalleryFolderViewModelFactory $folderViewModelFactory)
    {
    }

    public function create(GalleryOverview $overview, PageModel $page, ModuleModel $module): GalleryOverviewViewModel
    {
        $folders = $this->getVisibleFolders($overview->folders);
        $folders = array_map(
            fn (GalleryFolder $folder) => $this->folderViewModelFactory->create($folder, $page, $module->galleryCoverImageSize),
            $folders,
        );

        return new GalleryOverviewViewModel(
            folders: $folders,
            introText: $module->galleryOverviewMessage,
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
