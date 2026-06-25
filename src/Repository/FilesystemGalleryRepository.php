<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Repository;

use Cgoit\ContaoFolderGalleryBundle\Loader\GalleryImageLoaderInterface;
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataReader;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
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
    ) {
    }

    public function findOverview(string $rootPath, bool $blnShowUnpublished = false): GalleryOverview
    {
        $folders = [];
        $folderIndex = [];

        foreach ($this->getDirectories($rootPath) as $subFolder) {
            $folder = $this->createFolder($subFolder, $folderIndex, [], true, $blnShowUnpublished);

            if (null !== $folder) {
                $folders[] = $folder;
            }
        }

        $metadata = $this->metadataLoader->read($rootPath);
        $folders = $this->sortFoldersByTitle($folders, $metadata);

        return new GalleryOverview($rootPath, $folders, $folderIndex);
    }

    /**
     * @param array<string>                $parentTrail
     * @param array<string, GalleryFolder> $folderIndex
     */
    private function createFolder(string $directory, array &$folderIndex, array $parentTrail = [], bool $recursive = true, bool $blnShowUnpublished = false): GalleryFolder|null
    {
        $metadata = $this->metadataLoader->read($directory);

        if (!$blnShowUnpublished && !$metadata->isPublished()) {
            return null;
        }

        $slug = $this->slug->generate($metadata->title ?? basename($directory));
        $trail = [
            ...$parentTrail,
            $slug,
        ];

        $folders = [];

        if ($recursive) {
            foreach ($this->getDirectories($directory) as $subFolder) {
                $folder = $this->createFolder($subFolder, $folderIndex, $trail, $recursive, $blnShowUnpublished);

                if (null !== $folder) {
                    $folders[] = $folder;
                }
            }
        }

        $folders = $this->sortFoldersByTitle($folders, $metadata);

        $folder = new GalleryFolder(
            slug: $slug,
            title: $metadata->title ?? basename($directory),
            filesystemDirectory: $directory,
            trail: $trail,
            metadata: $metadata,
            folders: $folders,
            images: $this->galleryImageLoader->loadImages(
                $directory,
                $metadata->cover,
            ),
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

        sort($directories);

        return $directories;
    }

    /**
     * @param list<GalleryFolder> $folders
     *
     * @return list<GalleryFolder>
     */
    private function sortFoldersByTitle(array $folders, GalleryMetadata $metadata): array
    {
        usort(
            $folders,
            static fn (GalleryFolder $a, GalleryFolder $b): int => match ($metadata->sortOrder) {
                SortOrder::Asc => strcmp($a->title, $b->title),
                SortOrder::Desc => strcmp($b->title, $a->title),
            },
        );

        return $folders;
    }
}
