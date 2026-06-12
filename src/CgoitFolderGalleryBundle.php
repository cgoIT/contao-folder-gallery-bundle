<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class CgoitFolderGalleryBundle extends AbstractBundle
{
    #[\Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
