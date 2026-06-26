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

use Symfony\Contracts\Cache\TagAwareCacheInterface;

final readonly class GalleryCacheInvalidator
{
    public function __construct(private TagAwareCacheInterface $cache)
    {
    }

    public function invalidate(): void
    {
        $this->cache->invalidateTags(GalleryCache::getAllTags());
    }
}
