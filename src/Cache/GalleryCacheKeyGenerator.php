<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Cache;

final readonly class GalleryCacheKeyGenerator
{
    public function allOverviews(bool $showUnpublished): string
    {
        return \sprintf('all_folder_gallery_overviews_%d', (int) $showUnpublished);
    }

    /**
     * @return array<string>
     */
    public function allKeys(): array
    {
        return [
            $this->allOverviews(false),
            $this->allOverviews(true),
        ];
    }
}
