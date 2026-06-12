<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Model;

final readonly class GalleryYear
{
    /**
     * @param list<GalleryDay> $days
     */
    public function __construct(
        public string $slug,
        public string $title,
        public \DateTimeImmutable|null $publishedFrom,
        public array $days,
    ) {
    }
}
