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

use Cgoit\ContaoFolderGalleryBundle\FrontendModule\FolderGalleryModule;
use Contao\FilesModel;
use Contao\ModuleModel;

final class ContaoGalleryRootProvider implements GalleryRootProviderInterface
{
    /**
     * @return array<string>
     */
    public function getGalleryRoots(): array
    {
        $roots = [];

        $modules = ModuleModel::findBy('type', FolderGalleryModule::TYPE);

        if (null === $modules) {
            return [];
        }

        foreach ($modules as $module) {
            $root = FilesModel::findById($module->galleryRoot);

            if (!$root instanceof FilesModel) {
                continue;
            }

            $roots[] = $root->path;
        }

        return array_values(array_unique($roots));
    }
}
