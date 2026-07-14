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

final readonly class GalleryOverview
{
    /**
     * @param list<GalleryFolder>              $folders
     * @param array<string|int, GalleryFolder> $folderIndex
     */
    public function __construct(
        public GalleryRoot $root,
        public array $folders,
        public array $folderIndex,
    ) {
    }

    public function findFolderByPath(string $path): GalleryFolder|null
    {
        return $this->folderIndex[$path] ?? null;
    }

    /**
     * @param array<string> $trail
     */
    public function findFolderByTrail(array $trail): GalleryFolder|null
    {
        return $this->findFolderByPath(implode('/', $trail));
    }

    public function getModuleName(): string
    {
        return $this->root->moduleName;
    }

    public function getModuleId(): int
    {
        return $this->root->moduleId;
    }
}
