<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\EventListener;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\Matcher\GalleryPathMatcher;
use Contao\CoreBundle\Filesystem\Dbafs\DbafsChangeEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final readonly class GalleryCacheInvalidateListener
{
    public function __construct(
        private GalleryCacheInvalidator $invalidator,
        private GalleryPathMatcher $pathMatcher,
    ) {
    }

    public function __invoke(DbafsChangeEvent $event): void
    {
        if (!$this->pathMatcher->affectsGallery($event->getChangeSet())) {
            return;
        }

        $this->invalidator->invalidate();
    }
}
