<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Security;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Decides which parts of the galleries a backend user may see and edit in the metadata editor.
 *
 * Access requires the backend module "folder_gallery" (user group permission) and, per folder,
 * a file mount that covers the folder.
 */
final readonly class GalleryBackendAccess
{
    public const string MODULE = 'folder_gallery';

    public function __construct(private Security $security)
    {
    }

    public function canAccessModule(): bool
    {
        return $this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, self::MODULE);
    }

    /**
     * Checks that the path is a plain, normalized relative path. The file mount check of Contao
     * only compares path prefixes, so a path like "files/mounted/../../config" would pass it.
     */
    public static function isSafePath(string $path): bool
    {
        if ('' === $path || str_contains($path, "\0") || str_contains($path, '\\')) {
            return false;
        }

        foreach (explode('/', $path) as $segment) {
            if ('' === $segment || '.' === $segment || '..' === $segment) {
                return false;
            }
        }

        return true;
    }

    public function canEditFolder(string $path): bool
    {
        return self::isSafePath($path)
            && $this->canAccessModule()
            && $this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_PATH, $path);
    }

    /**
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessModuleGranted(): void
    {
        if (!$this->canAccessModule()) {
            throw new AccessDeniedException('Not enough permissions to access the folder gallery module.');
        }
    }

    /**
     * Only folders that are part of a gallery and covered by the user's file mounts may be edited,
     * so that metadata files cannot be read or written anywhere else.
     *
     * @param array<string, true> $editablePaths see findEditablePaths()
     *
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessFolderEditable(string $path, array $editablePaths): void
    {
        if (!self::isSafePath($path) || !isset($editablePaths[$path])) {
            throw new AccessDeniedException('Not enough permissions to edit the gallery folder.');
        }
    }

    /**
     * Reduces the overviews to the folders the user may edit. Folders without access are kept
     * only if they lead to an accessible subfolder, so that the tree hierarchy stays intact.
     *
     * @param list<GalleryOverview> $overviews
     *
     * @return list<GalleryOverview>
     */
    public function filterOverviews(array $overviews): array
    {
        if (!$this->canAccessModule()) {
            return [];
        }

        $filtered = [];

        foreach ($overviews as $overview) {
            $folders = $this->filterFolders($overview->folders);

            if ([] === $folders) {
                continue;
            }

            $index = [];
            $this->indexFolders($folders, $index);

            $filtered[] = new GalleryOverview($overview->root, $folders, $index);
        }

        return $filtered;
    }

    /**
     * Returns the folder paths (as keys) of the given overviews that the user may edit.
     *
     * @param list<GalleryOverview> $overviews
     *
     * @return array<string, true>
     */
    public function findEditablePaths(array $overviews): array
    {
        $paths = [];

        foreach ($overviews as $overview) {
            foreach ($overview->folderIndex as $folder) {
                if ($this->canEditFolder($folder->filesystemDirectory)) {
                    $paths[$folder->filesystemDirectory] = true;
                }
            }
        }

        return $paths;
    }

    /**
     * @param list<GalleryFolder> $folders
     *
     * @return list<GalleryFolder>
     */
    private function filterFolders(array $folders): array
    {
        $result = [];

        foreach ($folders as $folder) {
            $children = $this->filterFolders($folder->folders);

            if ([] === $children && !$this->canEditFolder($folder->filesystemDirectory)) {
                continue;
            }

            $result[] = new GalleryFolder(
                $folder->slug,
                $folder->title,
                $folder->filesystemDirectory,
                $folder->trail,
                $folder->metadata,
                $children,
                $folder->images,
            );
        }

        return $result;
    }

    /**
     * @param list<GalleryFolder>              $folders
     * @param array<string|int, GalleryFolder> $index
     */
    private function indexFolders(array $folders, array &$index): void
    {
        foreach ($folders as $folder) {
            $index[$folder->getPath()] = $folder;
            $this->indexFolders($folder->folders, $index);
        }
    }
}
