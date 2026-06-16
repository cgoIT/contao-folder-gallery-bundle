<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Image\ImageSizes;
use Symfony\Bundle\SecurityBundle\Security;

class ModuleCallbacks extends Backend
{
    public function __construct(
        private readonly Security $security,
        private readonly ImageSizes $imageSizes,
    ) {
    }

    /**
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_module', target: 'fields.galleryCoverSize.options')]
    public function getImageSizes(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser) {
            return [];
        }

        return $this->imageSizes->getOptionsForUser($user);
    }
}
