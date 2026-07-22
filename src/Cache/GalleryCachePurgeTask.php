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

use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final readonly class GalleryCachePurgeTask
{
    public function __construct(private GalleryCacheInvalidator $galleryCacheInvalidator)
    {
    }

    public function __invoke(): void
    {
        $this->galleryCacheInvalidator->invalidate();
    }
}
