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

final class GalleryCache
{
    public const string PREFIX = 'folder_gallery';

    public const string TAG_OVERVIEWS = self::PREFIX.'.overviews';

    public const string KEY_ALL_OVERVIEWS = self::PREFIX.'.overviews.all';

    public const string KEY_PUBLISHED_OVERVIEWS = self::PREFIX.'.overviews.published';

    /**
     * @codeCoverageIgnore
     *
     * @return list<string>
     */
    public static function getAllTags(): array
    {
        return [
            self::TAG_OVERVIEWS,
        ];
    }
}
