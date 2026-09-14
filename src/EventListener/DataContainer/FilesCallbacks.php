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

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\Matcher\GalleryPathMatcher;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\Filesystem\Path;

final readonly class FilesCallbacks
{
    /**
     * @param array<string> $validImageExtensions
     */
    public function __construct(
        private array $validImageExtensions,
        private GalleryPathMatcher $pathMatcher,
        private GalleryCacheInvalidator $galleryCacheInvalidator,
    ) {
    }

    #[AsCallback(table: 'tl_files', target: 'config.onpalette')]
    public function addHideInGalleryField(string $palette, DataContainer $dc): string
    {
        if (!\in_array(Path::getExtension((string) $dc->id, true), $this->validImageExtensions, true)) {
            return $palette;
        }

        return PaletteManipulator::create()
            ->addField('hideInGallery', 'importantPartHeight', PaletteManipulator::POSITION_AFTER)
            ->applyToString($palette)
        ;
    }

    /**
     * The gallery overview cache is keyed by a filesystem fingerprint (paths and mtimes) and
     * otherwise only invalidated on real DBAFS filesystem changes. Saving hideInGallery (a plain
     * tl_files column) is neither, so without this callback the cache would keep serving the
     * previous image list until an unrelated filesystem change happens to bust it.
     */
    #[AsCallback(table: 'tl_files', target: 'config.onsubmit')]
    public function invalidateGalleryCacheOnSave(DataContainer $dc): void
    {
        if (!$this->pathMatcher->matchesPath((string) $dc->id)) {
            return;
        }

        $this->galleryCacheInvalidator->invalidate();
    }
}
