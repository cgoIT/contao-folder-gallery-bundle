<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Repository;

use Cgoit\ContaoFolderGalleryBundle\Loader\GalleryImageLoaderInterface;
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataReader;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Contao\CoreBundle\Filesystem\Dbafs\DbafsManager;
use Contao\CoreBundle\Slug\Slug;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Filesystem\Path;

#[AsAlias(GalleryRepositoryInterface::class)]
final readonly class FilesystemGalleryRepository implements GalleryRepositoryInterface
{
    public function __construct(
        private GalleryMetadataReader $metadataLoader,
        private GalleryImageLoaderInterface $galleryImageLoader,
        private Slug $slug,
        private DbafsManager $dbafsManager,
    ) {
    }

    public function findOverview(GalleryRoot $root): GalleryOverview
    {
        $folders = [];
        $folderIndex = [];

        $rootPath = $root->filesystemDirectory;

        foreach ($this->getDirectories($rootPath) as $subFolder) {
            $folder = $this->createFolder($subFolder, $folderIndex, [], true);

            $folders[] = $folder;
        }

        $metadata = $this->metadataLoader->read($rootPath);
        $folders = $this->sortFolders($folders, $metadata);

        return new GalleryOverview($root, $folders, $folderIndex);
    }

    /**
     * @param array<string>                $parentTrail
     * @param array<string, GalleryFolder> $folderIndex
     */
    private function createFolder(string $directory, array &$folderIndex, array $parentTrail = [], bool $recursive = true): GalleryFolder
    {
        $metadata = $this->metadataLoader->read($directory);

        $slug = $this->slug->generate($metadata->title ?? basename($directory), [], null, '');
        $trail = [
            ...$parentTrail,
            $slug,
        ];

        $folders = [];

        if ($recursive) {
            foreach ($this->getDirectories($directory) as $subFolder) {
                $folder = $this->createFolder($subFolder, $folderIndex, $trail, $recursive);

                $folders[] = $folder;
            }
        }

        $folders = $this->sortFolders($folders, $metadata);

        // sync filesystem directory to dbafs to ensure dbafs is up to date before reading images
        $this->dbafsManager->sync($directory);

        $images = $this->sortImages(
            $this->galleryImageLoader->loadImages($directory, $metadata->cover),
            $metadata,
        );

        $folder = new GalleryFolder(
            slug: $slug,
            title: $metadata->title ?? basename($directory),
            filesystemDirectory: $directory,
            trail: $trail,
            metadata: $metadata,
            folders: $folders,
            images: $images,
        );

        $folderIndex[$folder->getPath()] = $folder;

        return $folder;
    }

    /**
     * @return list<string>
     */
    private function getDirectories(string $directory): array
    {
        $directories = glob(
            Path::join($directory, '*'),
            GLOB_ONLYDIR,
        );

        if (false === $directories) {
            return [];
        }

        sort($directories, SORT_NATURAL | SORT_FLAG_CASE);

        return $directories;
    }

    /**
     * @param list<GalleryFolder> $folders
     *
     * @return list<GalleryFolder>
     */
    private function sortFolders(array $folders, GalleryMetadata $metadata): array
    {
        usort(
            $folders,
            static fn (GalleryFolder $a, GalleryFolder $b): int => match ($metadata->sortOrder) {
                SortOrder::Asc => strnatcasecmp(basename($a->filesystemDirectory), basename($b->filesystemDirectory)),
                SortOrder::Desc => strnatcasecmp(basename($b->filesystemDirectory), basename($a->filesystemDirectory)),
            },
        );

        return $folders;
    }

    /**
     * @param list<GalleryImage> $images
     *
     * @return list<GalleryImage>
     */
    private function sortImages(array $images, GalleryMetadata $metadata): array
    {
        usort(
            $images,
            static fn (GalleryImage $a, GalleryImage $b): int => match ($metadata->sortOrder) {
                SortOrder::Asc => strnatcasecmp($a->filename, $b->filename),
                SortOrder::Desc => strnatcasecmp($b->filename, $a->filename),
            },
        );

        return $images;
    }
}
