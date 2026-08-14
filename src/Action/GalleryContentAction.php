<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Action;

final readonly class GalleryContentAction
{
    public function __construct(
        public string $label,
        public string $url,
        public string|null $title = null,
        public string|null $icon = null,
        public string|null $target = null,
        public string|null $rel = null,
    ) {
    }
}
