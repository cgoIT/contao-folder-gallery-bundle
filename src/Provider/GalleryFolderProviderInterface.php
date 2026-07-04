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

interface GalleryFolderProviderInterface
{
    /**
     * @return list<GalleryOverview>
     */
    public function findAllOverviews(bool $blnShowUnpublished = false): array;

    public function findOverviewByRootPath(string $path, bool $blnShowUnpublished = false): GalleryOverview|null;

    public function findFolderByPath(string $path, bool $blnShowUnpublished = false): GalleryFolder|null;
}
